<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\KycEvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KycController extends Controller
{
    public function __construct(
        private readonly KycEvaluationService $kycEvaluationService,
    ) {
    }

    /**
     * Obtener el estado actual de KYC del perfil autenticado.
     */
    public function status(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        return response()->json([
            'kyc_status' => $profile->kyc_status ?? 'no_verified',
            'kyc_rejection_reason' => $profile->kyc_rejection_reason,
            'has_document' => ! empty($profile->kyc_doc_front_path),
            'has_rif' => ! empty($profile->kyc_rif_path),
            'has_selfie' => ! empty($profile->kyc_selfie_path),
            'has_selfie_with_doc' => ! empty($profile->kyc_selfie_with_doc_path),
            'kyc_document_type' => $profile->kyc_document_type,
            'kyc_country_code' => $profile->kyc_country_code,
        ]);
    }

    /**
     * Iniciar o reiniciar el flujo KYC.
     */
    public function start(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        if (in_array($profile->kyc_status, [null, 'no_verified', 'rejected'], true)) {
            $profile->kyc_status = 'pending';
        }

        // Para el MVP asumimos CI venezolana y país VE
        $profile->kyc_document_type = $request->input('document_type', $profile->kyc_document_type ?? 'ci_ve');
        $profile->kyc_document_number = $request->input('document_number', $profile->kyc_document_number ?? $profile->ci_number);
        $profile->kyc_country_code = $request->input('country_code', $profile->kyc_country_code ?? 'VE');

        $profile->save();

        return response()->json([
            'message' => 'Flujo KYC iniciado.',
            'kyc_status' => $profile->kyc_status,
        ]);
    }

    /**
     * Subir documento de identidad (CI frontal) y RIF.
     */
    public function uploadDocument(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        $validated = $request->validate([
            'front' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'rif' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'document_type' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'country_code' => 'nullable|string|max:5',
        ]);

        $disk = 'public';
        $basePath = 'kyc/' . $profile->id;
        $baseUrl = env('APP_ENV') === 'production'
            ? env('APP_URL_PRODUCTION')
            : env('APP_URL_LOCAL');

        if (isset($validated['front'])) {
            // Usar nombre basado en ID de usuario: kyc/{user_id}/ci_front.jpg
            $extension = $validated['front']->getClientOriginalExtension();
            $frontPath = $validated['front']->storeAs($basePath, "user_{$profile->id}_ci_front.{$extension}", $disk);
            $profile->kyc_doc_front_path = $frontPath;
        }

        if (isset($validated['rif'])) {
            // Usar nombre basado en ID de usuario: kyc/{user_id}/rif.jpg
            $extension = $validated['rif']->getClientOriginalExtension();
            $rifPath = $validated['rif']->storeAs($basePath, "user_{$profile->id}_rif.{$extension}", $disk);
            $profile->kyc_rif_path = $rifPath;
        }

        // Actualizar metadatos si se envían
        if (! empty($validated['document_type'])) {
            $profile->kyc_document_type = $validated['document_type'];
        }
        if (! empty($validated['document_number'])) {
            $profile->kyc_document_number = $validated['document_number'];
        }
        if (! empty($validated['country_code'])) {
            $profile->kyc_country_code = $validated['country_code'];
        }

        // Marcar como pending si aún no tiene estado
        if (! $profile->kyc_status || $profile->kyc_status === 'no_verified') {
            $profile->kyc_status = 'pending';
        }

        $profile->save();

        $this->maybeAutoVerify($profile);

        return response()->json([
            'message' => 'Documentos KYC (CI y RIF) subidos correctamente.',
            'kyc_status' => $profile->kyc_status,
        ]);
    }

    /**
     * Subir selfie del usuario (también se guarda como foto de perfil).
     */
    public function uploadSelfie(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        $validated = $request->validate([
            'selfie' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $disk = 'public';
        $basePath = 'kyc/' . $profile->id;
        $baseUrl = env('APP_ENV') === 'production'
            ? env('APP_URL_PRODUCTION')
            : env('APP_URL_LOCAL');

        // Guardar selfie para KYC con nombre basado en ID de usuario
        $extension = $validated['selfie']->getClientOriginalExtension();
        $selfiePath = $validated['selfie']->storeAs($basePath, "user_{$profile->id}_selfie.{$extension}", $disk);
        $profile->kyc_selfie_path = $selfiePath;

        // También guardar como foto de perfil si no tiene una
        if (empty($profile->photo_users)) {
            $photoPath = $validated['selfie']->store('profile_images', $disk);
            $profile->photo_users = $baseUrl . '/storage/' . $photoPath;
        }

        if (! $profile->kyc_status || $profile->kyc_status === 'no_verified') {
            $profile->kyc_status = 'pending';
        }

        $profile->save();

        $this->maybeAutoVerify($profile);

        return response()->json([
            'message' => 'Selfie KYC subida correctamente.',
            'kyc_status' => $profile->kyc_status,
        ]);
    }

    /**
     * Subir selfie sosteniendo el documento.
     */
    public function uploadSelfieWithDoc(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        $validated = $request->validate([
            'selfie_with_doc' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $disk = 'public';
        $basePath = 'kyc/' . $profile->id;

        // Usar nombre basado en ID de usuario: kyc/{user_id}/selfie_with_doc.jpg
        $extension = $validated['selfie_with_doc']->getClientOriginalExtension();
        $selfieWithDocPath = $validated['selfie_with_doc']->storeAs($basePath, "user_{$profile->id}_selfie_with_doc.{$extension}", $disk);
        $profile->kyc_selfie_with_doc_path = $selfieWithDocPath;

        if (! $profile->kyc_status || $profile->kyc_status === 'no_verified') {
            $profile->kyc_status = 'pending';
        }

        $profile->save();

        $this->maybeAutoVerify($profile);

        return response()->json([
            'message' => 'Selfie con documento KYC subida correctamente.',
            'kyc_status' => $profile->kyc_status,
        ]);
    }

    /**
     * Subir selfies del liveness detection (hasta 5 selfies).
     */
    public function uploadLivenessSelfies(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        // Validar que se recibieron archivos
        $selfies = $request->allFiles();
        
        if (empty($selfies) || !isset($selfies['selfies'])) {
            return response()->json([
                'message' => 'No se recibieron selfies del liveness.',
            ], 422);
        }

        // Obtener el array de selfies (puede venir como selfies[0], selfies[1], etc.)
        $selfiesArray = is_array($selfies['selfies']) ? $selfies['selfies'] : [$selfies['selfies']];
        
        // Validar cada selfie
        foreach ($selfiesArray as $selfie) {
            if (!$selfie->isValid()) {
                return response()->json([
                    'message' => 'Una o más selfies no son válidas.',
                ], 422);
            }
            $extension = strtolower($selfie->getClientOriginalExtension());
            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                return response()->json([
                    'message' => 'Las selfies deben ser imágenes JPG, JPEG o PNG.',
                ], 422);
            }
            if ($selfie->getSize() > 5 * 1024 * 1024) { // 5MB
                return response()->json([
                    'message' => 'Una o más selfies exceden el tamaño máximo de 5MB.',
                ], 422);
            }
        }

        $disk = 'public';
        $basePath = 'kyc/' . $profile->id;
        $livenessPaths = [];

        // Guardar cada selfie del liveness con nombre basado en ID de usuario
        foreach ($selfiesArray as $index => $selfie) {
            $extension = $selfie->getClientOriginalExtension();
            $livenessPath = $selfie->storeAs(
                $basePath,
                "user_{$profile->id}_liveness_" . ($index + 1) . ".{$extension}",
                $disk
            );
            $livenessPaths[] = $livenessPath;
        }

        // Guardar las rutas en JSON
        $profile->kyc_liveness_selfies_paths = $livenessPaths;
        
        if (! $profile->kyc_status || $profile->kyc_status === 'no_verified') {
            $profile->kyc_status = 'pending';
        }

        $profile->save();

        $this->maybeAutoVerify($profile);

        return response()->json([
            'message' => 'Selfies del liveness detection subidas correctamente.',
            'count' => count($livenessPaths),
            'kyc_status' => $profile->kyc_status,
        ]);
    }

    /**
     * Obtener el perfil asociado al usuario autenticado.
     */
    private function getAuthenticatedProfile(Request $request): ?Profile
    {
        $user = $request->user() ?? Auth::user();

        if (! $user) {
            return null;
        }

        return $user->profile;
    }

    /**
     * Extraer datos de CI y RIF usando Gemini AI y comparar con OCR.
     * Si los datos no coinciden, se priorizan los datos de Gemini.
     */
    public function extractDocumentDataWithGemini(Request $request): JsonResponse
    {
        $profile = $this->getAuthenticatedProfile($request);

        if (! $profile) {
            return response()->json([
                'message' => 'Perfil no encontrado para el usuario autenticado.',
            ], 404);
        }

        $validated = $request->validate([
            'ci_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'rif_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            // Datos del OCR para comparar
            'ocr_ci_data' => 'nullable|array',
            'ocr_rif_data' => 'nullable|array',
        ]);

        try {
            // Convertir imágenes a base64
            $ciBase64 = $this->imageToBase64($validated['ci_image']);
            $rifBase64 = $this->imageToBase64($validated['rif_image']);

            if (!$ciBase64 || !$rifBase64) {
                return response()->json([
                    'message' => 'Error al procesar las imágenes.',
                ], 422);
            }

            // Llamar a Gemini para extraer datos
            $geminiData = $this->callGeminiForDataExtraction($ciBase64, $rifBase64);

            if ($geminiData === null) {
                // Si Gemini falla, usar datos del OCR como fallback
                return response()->json([
                    'message' => 'No se pudo extraer datos con IA. Usando datos del OCR.',
                    'data' => [
                        'ci' => $validated['ocr_ci_data'] ?? [],
                        'rif' => $validated['ocr_rif_data'] ?? [],
                    ],
                    'source' => 'ocr_fallback',
                ]);
            }

            // Comparar datos de Gemini con OCR
            $ocrCiData = $validated['ocr_ci_data'] ?? [];
            $ocrRifData = $validated['ocr_rif_data'] ?? [];

            $finalCiData = $this->compareAndMergeData(
                $geminiData['ci'] ?? [],
                $ocrCiData,
                'ci'
            );

            $finalRifData = $this->compareAndMergeData(
                $geminiData['rif'] ?? [],
                $ocrRifData,
                'rif'
            );

            // Guardar datos extraídos en storage para pre-llenar formularios
            $storage = Storage::disk('local');
            $storage->put(
                "kyc_extracted_data_{$profile->id}.json",
                json_encode([
                    'ci' => $finalCiData,
                    'rif' => $finalRifData,
                    'source' => 'gemini',
                    'extracted_at' => now()->toIso8601String(),
                ])
            );

            return response()->json([
                'message' => 'Datos extraídos exitosamente con IA.',
                'data' => [
                    'ci' => $finalCiData,
                    'rif' => $finalRifData,
                ],
                'comparison' => [
                    'ci_matched' => $this->dataMatches($finalCiData, $ocrCiData),
                    'rif_matched' => $this->dataMatches($finalRifData, $ocrRifData),
                ],
                'source' => 'gemini',
            ]);
        } catch (\Exception $e) {
            Log::error('Error extrayendo datos con Gemini', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
            ]);

            // Fallback a OCR si hay error
            return response()->json([
                'message' => 'Error al extraer datos con IA. Usando datos del OCR.',
                'data' => [
                    'ci' => $validated['ocr_ci_data'] ?? [],
                    'rif' => $validated['ocr_rif_data'] ?? [],
                ],
                'source' => 'ocr_fallback',
            ], 200); // 200 porque aún retornamos datos válidos (OCR)
        }
    }

    /**
     * Convertir imagen a base64
     */
    private function imageToBase64($imageFile): ?string
    {
        try {
            $imageData = file_get_contents($imageFile->getRealPath());
            $mimeType = $imageFile->getMimeType();
            $base64 = base64_encode($imageData);
            return "data:{$mimeType};base64,{$base64}";
        } catch (\Exception $e) {
            Log::error('Error convirtiendo imagen a base64', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Llamar a Gemini para extraer datos estructurados de CI y RIF
     */
    private function callGeminiForDataExtraction(string $ciBase64, string $rifBase64): ?array
    {
        $apiKey = config('services.google_gen_ai.api_key');

        if (empty($apiKey) || $apiKey === 'replace-me') {
            Log::info('KycController: Gemini no disponible (sin API key)');
            return null;
        }

        $baseUrl = rtrim(config('services.google_gen_ai.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = ltrim(config('services.google_gen_ai.model', 'models/gemini-2.0-flash'), '/');
        $endpoint = sprintf('%s/%s:generateContent', $baseUrl, $model);

        // Extraer base64 sin prefijo
        $ciBase64Data = preg_replace('/^data:[^;]+;base64,/', '', $ciBase64);
        $rifBase64Data = preg_replace('/^data:[^;]+;base64,/', '', $rifBase64);

        $prompt = <<<PROMPT
Eres un experto en extracción de datos de documentos venezolanos.

Analiza las dos imágenes proporcionadas:
1. **CI (Cédula de Identidad)**: Primera imagen
2. **RIF (Registro de Información Fiscal)**: Segunda imagen

Extrae los siguientes datos de cada documento:

**Para la CI (Cédula de Identidad):**
- firstName: Primer nombre
- lastName: Apellido (puede ser compuesto)
- ciNumber: Número de cédula (formato: V-12345678 o E-12345678)
- dateOfBirth: Fecha de nacimiento (formato: YYYY-MM-DD)

**Para el RIF:**
- businessName: Razón social o nombre del negocio
- rifNumber: Número de RIF (formato: V-12345678-9 o J-12345678-9)
- address: Dirección completa (calle, ciudad, estado)

Responde EXCLUSIVAMENTE con un JSON válido, sin texto extra ni bloques markdown:

{
  "ci": {
    "firstName": "string o null",
    "lastName": "string o null",
    "ciNumber": "string o null",
    "dateOfBirth": "YYYY-MM-DD o null"
  },
  "rif": {
    "businessName": "string o null",
    "rifNumber": "string o null",
    "address": "string o null"
  }
}

Si algún dato no es legible o no existe, usa null para ese campo.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-goog-api-key' => $apiKey,
            ])->timeout(60)->post($endpoint, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $ciBase64Data,
                                ],
                            ],
                            [
                                'inline_data' => [
                                    'mime_type' => 'image/jpeg',
                                    'data' => $rifBase64Data,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 1024,
                ],
            ]);

            if (!$response->successful()) {
                Log::warning('KycController: Gemini request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $rawText = data_get($data, 'candidates.0.content.parts.0.text');

            if (!$rawText) {
                Log::warning('KycController: Gemini no devolvió texto');
                return null;
            }

            // Decodificar JSON de la respuesta
            $clean = trim($rawText);
            $clean = preg_replace('/```json\s*/', '', $clean);
            $clean = preg_replace('/```\s*/', '', $clean);
            $clean = trim($clean);

            $decoded = json_decode($clean, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('KycController: Error decodificando JSON de Gemini', [
                    'error' => json_last_error_msg(),
                    'raw_text' => substr($rawText, 0, 500),
                ]);
                return null;
            }

            return $decoded;
        } catch (\Exception $e) {
            Log::error('KycController: Error llamando a Gemini', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Comparar y fusionar datos de Gemini con OCR
     * Si no coinciden, priorizar Gemini
     */
    private function compareAndMergeData(array $geminiData, array $ocrData, string $type): array
    {
        // Si Gemini no tiene datos, usar OCR
        if (empty($geminiData)) {
            return $ocrData;
        }

        // Si OCR no tiene datos, usar Gemini
        if (empty($ocrData)) {
            return $geminiData;
        }

        // Comparar campo por campo
        $finalData = [];

        if ($type === 'ci') {
            $fields = ['firstName', 'lastName', 'ciNumber', 'dateOfBirth'];
        } else {
            $fields = ['businessName', 'rifNumber', 'address'];
        }

        foreach ($fields as $field) {
            $geminiValue = $geminiData[$field] ?? null;
            $ocrValue = $ocrData[$field] ?? null;

            // Si ambos tienen valores y coinciden, usar cualquiera
            if ($geminiValue && $ocrValue && $this->normalizeValue($geminiValue) === $this->normalizeValue($ocrValue)) {
                $finalData[$field] = $geminiValue; // Usar Gemini (más confiable)
            } elseif ($geminiValue) {
                // Si solo Gemini tiene valor o no coinciden, usar Gemini
                $finalData[$field] = $geminiValue;
            } elseif ($ocrValue) {
                // Si solo OCR tiene valor, usar OCR
                $finalData[$field] = $ocrValue;
            } else {
                $finalData[$field] = null;
            }
        }

        return $finalData;
    }

    /**
     * Normalizar valor para comparación (quitar espacios, mayúsculas, etc.)
     */
    private function normalizeValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return strtoupper(trim($value));
    }

    /**
     * Verificar si los datos coinciden
     */
    private function dataMatches(array $data1, array $data2): bool
    {
        foreach ($data1 as $key => $value1) {
            $value2 = $data2[$key] ?? null;
            if ($this->normalizeValue($value1) !== $this->normalizeValue($value2)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Intentar marcar el perfil como verificado automáticamente
     * cuando se cumplan las condiciones mínimas de KYC.
     */
    private function maybeAutoVerify(Profile $profile): void
    {
        $this->kycEvaluationService->evaluate($profile);
    }
}


