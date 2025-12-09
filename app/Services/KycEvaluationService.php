<?php

namespace App\Services;

use App\Models\Profile;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KycEvaluationService
{
    /**
     * Evaluar automáticamente el KYC de un perfil y, si aplica,
     * actualizar su estado a "verified" o "rejected".
     *
     * Flujo:
     * 1. Validaciones locales (formato CI, RIF, imágenes)
     * 2. Si pasa validaciones locales, llama a Gemini para evaluación inteligente
     * 3. Si Gemini no está disponible, usa decisión local
     * 4. Actualiza kyc_status según decisión final
     */
    public function evaluate(Profile $profile): void
    {
        // Si ya está verificado, no hacer nada
        if ($profile->kyc_status === 'verified') {
            return;
        }

        // Paso 1: Validaciones locales
        $localValidation = $this->validateLocally($profile);

        if (!$localValidation['valid']) {
            // Si falla validación local, no llamar a Gemini
            $profile->kyc_status = 'pending';
            $profile->kyc_rejection_reason = $localValidation['reason'] ?? null;
            $profile->save();
            return;
        }

        // Paso 2: Evaluación con Gemini (si está disponible)
        $geminiDecision = $this->evaluateWithGemini($profile);

        // Paso 3: Decisión final
        if ($geminiDecision !== null) {
            // Usar decisión de Gemini
            $profile->kyc_status = $geminiDecision['status'];
            if ($geminiDecision['status'] === 'verified') {
                $profile->kyc_verified_at = now();
                $profile->kyc_rejection_reason = null; // Limpiar razón si está verificado
            } else {
                $profile->kyc_rejection_reason = $geminiDecision['reason'];
            }
        } else {
            // Fallback: usar decisión local (si pasó validaciones, verificar)
            $profile->kyc_status = 'verified';
            $profile->kyc_verified_at = now();
            $profile->kyc_rejection_reason = null;
        }

        $profile->save();

        // Paso 4: Enviar notificación si el estado cambió a verified o rejected
        $this->sendKycStatusNotification($profile);
    }

    /**
     * Enviar notificación push cuando el estado KYC cambia
     */
    private function sendKycStatusNotification(Profile $profile): void
    {
        if (empty($profile->fcm_device_token)) {
            return; // No hay token FCM, no enviar notificación
        }

        try {
            $firebaseService = new FirebaseService();
            $user = $profile->user;

            switch ($profile->kyc_status) {
                case 'verified':
                    $firebaseService->sendToDevice(
                        $profile->fcm_device_token,
                        '✅ Verificación KYC completada',
                        'Tu identidad ha sido verificada exitosamente. Ya puedes publicar productos.',
                        [
                            'type' => 'kyc_verified',
                            'kyc_status' => 'verified',
                            'timestamp' => now()->timestamp,
                        ]
                    );
                    Log::info('📬 Notificación KYC verified enviada', [
                        'profile_id' => $profile->id,
                        'user_id' => $user->id ?? null,
                    ]);
                    break;

                case 'rejected':
                    $reason = $profile->kyc_rejection_reason ?? 'No se especificó razón';
                    $firebaseService->sendToDevice(
                        $profile->fcm_device_token,
                        '❌ Verificación KYC rechazada',
                        "Tu verificación fue rechazada: $reason. Puedes reintentar el proceso.",
                        [
                            'type' => 'kyc_rejected',
                            'kyc_status' => 'rejected',
                            'rejection_reason' => $reason,
                            'timestamp' => now()->timestamp,
                        ]
                    );
                    Log::info('📬 Notificación KYC rejected enviada', [
                        'profile_id' => $profile->id,
                        'user_id' => $user->id ?? null,
                        'reason' => $reason,
                    ]);
                    break;

                case 'pending':
                    // No enviar notificación para pending (es estado intermedio)
                    break;
            }
        } catch (\Exception $e) {
            Log::error('❌ Error enviando notificación KYC', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
            ]);
            // No lanzar excepción, solo loggear el error
        }
    }

    /**
     * Validaciones locales (formato, existencia de datos)
     */
    private function validateLocally(Profile $profile): array
    {
        // Validar CI venezolana básica: V-12345678 (7-8 dígitos)
        $ci = $profile->ci_number;
        $ciValid = is_string($ci) && preg_match('/^V-\d{7,8}$/', $ci);

        if (!$ciValid) {
            return [
                'valid' => false,
                'reason' => 'El número de cédula no tiene un formato válido (debe ser V-12345678).',
            ];
        }

        // Validar RIF de la hacienda principal: V-12345678-9 o J-12345678-9
        $primaryRanch = $profile->getPrimaryRanch();
        $rif = $primaryRanch?->tax_id;
        $rifValid = is_string($rif) && preg_match('/^(V|J)-\d{8}-\d$/', $rif);

        if (!$rifValid) {
            return [
                'valid' => false,
                'reason' => 'El RIF de la hacienda principal no tiene un formato válido (debe ser V-12345678-9 o J-12345678-9).',
            ];
        }

        // Validar que existan todas las imágenes mínimas
        $hasDocFront = !empty($profile->kyc_doc_front_path);
        $hasRif = !empty($profile->kyc_rif_path);
        $hasSelfie = !empty($profile->kyc_selfie_path);
        $hasSelfieWithDoc = !empty($profile->kyc_selfie_with_doc_path);

        $missingImages = [];
        if (!$hasDocFront) $missingImages[] = 'Cédula de identidad (frente)';
        if (!$hasRif) $missingImages[] = 'RIF';
        if (!$hasSelfie) $missingImages[] = 'Selfie';
        if (!$hasSelfieWithDoc) $missingImages[] = 'Selfie con documento';

        if (!empty($missingImages)) {
            return [
                'valid' => false,
                'reason' => 'Faltan las siguientes imágenes: ' . implode(', ', $missingImages) . '.',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Evaluar KYC usando Gemini IA
     *
     * @return array|null {status: 'verified'|'rejected'|'pending', reason: string|null}
     *                    null si Gemini no está disponible
     */
    private function evaluateWithGemini(Profile $profile): ?array
    {
        $apiKey = config('services.google_gen_ai.api_key');

        if (empty($apiKey) || $apiKey === 'replace-me') {
            Log::info('KycEvaluationService: Gemini no disponible (sin API key)');
            return null;
        }

        try {
            $kycPackage = $this->buildKycPackage($profile);
            $prompt = $this->buildKycPromptForGemini($kycPackage);
            
            // Preparar imágenes para enviar a Gemini
            $images = $this->prepareImagesForGemini($profile);
            
            $response = $this->callGemini($prompt, $images);

            if ($response === null) {
                Log::warning('KycEvaluationService: Gemini no respondió');
                return null;
            }

            return $this->parseGeminiResponse($response);
        } catch (\Exception $e) {
            Log::error('KycEvaluationService: Error llamando a Gemini', [
                'error' => $e->getMessage(),
                'profile_id' => $profile->id,
            ]);
            return null;
        }
    }

    /**
     * Armar el paquete KYC con todos los datos relevantes
     */
    private function buildKycPackage(Profile $profile): array
    {
        $primaryRanch = $profile->getPrimaryRanch();
        $address = $primaryRanch?->address;

        return [
            // Datos personales del perfil
            'profile' => [
                'first_name' => $profile->firstName,
                'middle_name' => $profile->middleName,
                'last_name' => $profile->lastName,
                'second_last_name' => $profile->secondLastName,
                'ci_number' => $profile->ci_number,
                'date_of_birth' => $profile->date_of_birth?->format('Y-m-d'),
                'sex' => $profile->sex,
                'kyc_document_type' => $profile->kyc_document_type,
                'kyc_document_number' => $profile->kyc_document_number,
                'kyc_country_code' => $profile->kyc_country_code,
            ],
            // Datos de la hacienda principal
            'ranch' => $primaryRanch ? [
                'name' => $primaryRanch->name,
                'legal_name' => $primaryRanch->legal_name,
                'tax_id' => $primaryRanch->tax_id,
            ] : null,
            // Dirección de la hacienda
            'address' => $address ? [
                'street' => $address->street,
                'city' => $address->city?->name,
                'state' => $address->state?->name,
                'country' => $address->country?->name,
            ] : null,
            // Flags de imágenes
            'images' => [
                'has_doc_front' => !empty($profile->kyc_doc_front_path),
                'has_rif' => !empty($profile->kyc_rif_path),
                'has_selfie' => !empty($profile->kyc_selfie_path),
                'has_selfie_with_doc' => !empty($profile->kyc_selfie_with_doc_path),
            ],
        ];
    }

    /**
     * Leer imagen del storage y convertirla a base64
     * Retorna el base64 con prefijo data: para uso en Gemini
     */
    private function imageToBase64(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            if (!$disk->exists($path)) {
                Log::warning('KycEvaluationService: Imagen no encontrada', ['path' => $path]);
                return null;
            }

            $imageData = $disk->get($path);
            $mimeType = $this->getMimeType($path);
            $base64 = base64_encode($imageData);

            // Retornar con prefijo data: para Gemini
            return "data:{$mimeType};base64,{$base64}";
        } catch (\Exception $e) {
            Log::error('KycEvaluationService: Error leyendo imagen', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Obtener MIME type basado en la extensión del archivo
     */
    private function getMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    /**
     * Preparar imágenes para enviar a Gemini
     * Retorna un array con las imágenes en base64 y su MIME type
     */
    private function prepareImagesForGemini(Profile $profile): array
    {
        $images = [];

        // 1. Selfie
        if (!empty($profile->kyc_selfie_path)) {
            $selfieBase64 = $this->imageToBase64($profile->kyc_selfie_path);
            if ($selfieBase64) {
                $images[] = [
                    'data' => $selfieBase64,
                    'mime_type' => $this->getMimeType($profile->kyc_selfie_path),
                    'type' => 'selfie',
                ];
            }
        }

        // 2. CI (Cédula de Identidad)
        if (!empty($profile->kyc_doc_front_path)) {
            $ciBase64 = $this->imageToBase64($profile->kyc_doc_front_path);
            if ($ciBase64) {
                $images[] = [
                    'data' => $ciBase64,
                    'mime_type' => $this->getMimeType($profile->kyc_doc_front_path),
                    'type' => 'ci',
                ];
            }
        }

        // 3. Selfie con CI
        if (!empty($profile->kyc_selfie_with_doc_path)) {
            $selfieWithDocBase64 = $this->imageToBase64($profile->kyc_selfie_with_doc_path);
            if ($selfieWithDocBase64) {
                $images[] = [
                    'data' => $selfieWithDocBase64,
                    'mime_type' => $this->getMimeType($profile->kyc_selfie_with_doc_path),
                    'type' => 'selfie_with_doc',
                ];
            }
        }

        // 4. Selfies del liveness detection (hasta 5)
        if (!empty($profile->kyc_liveness_selfies_paths) && is_array($profile->kyc_liveness_selfies_paths)) {
            foreach ($profile->kyc_liveness_selfies_paths as $index => $livenessPath) {
                $livenessBase64 = $this->imageToBase64($livenessPath);
                if ($livenessBase64) {
                    $images[] = [
                        'data' => $livenessBase64,
                        'mime_type' => $this->getMimeType($livenessPath),
                        'type' => 'liveness_' . ($index + 1),
                    ];
                }
            }
        }

        // Nota: RIF no se envía a Gemini para análisis facial, solo se usa para validación local

        return $images;
    }

    /**
     * Construir el prompt para Gemini con análisis de imágenes
     */
    private function buildKycPromptForGemini(array $kycPackage): string
    {
        $json = json_encode($kycPackage, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
Eres un experto en verificación de identidad (KYC) para una plataforma de marketplace de ganado en Venezuela.

Tu tarea es evaluar la consistencia y validez de los datos proporcionados Y analizar las imágenes para determinar si el usuario puede ser verificado automáticamente.

**Datos del usuario:**
{$json}

**Imágenes proporcionadas:**
- **CI (Cédula de Identidad)**: Imagen frontal del documento de identidad venezolano
- **RIF**: Imagen del Registro de Información Fiscal
- **Selfie**: Foto del rostro de la persona (selfie final después del liveness)
- **Selfie con CI**: Foto de la persona sosteniendo su cédula de identidad
- **Selfies del Liveness Detection (liveness_1 a liveness_5)**: Hasta 5 selfies capturadas durante la detección de liveness (poses: frontal, arriba, abajo, izquierda, derecha)

**Criterios de evaluación:**

1. **Análisis de imágenes (CRÍTICO):**

   a) **Verificar que las selfies son REALES (no fotos de fotos, pantallas, o dibujos):**
      - **Selfie principal**: Debe ser una foto REAL tomada directamente de una persona viva, no una foto de una foto, una captura de pantalla, un dibujo, o una imagen procesada. Verifica que tenga características de una foto real (iluminación natural, profundidad, textura de piel real).
      - **Selfies del liveness (liveness_1 a liveness_5)**: Cada una debe ser una foto REAL de la misma persona en diferentes poses. Verifica que todas sean fotos reales, no imágenes de pantalla o fotos de fotos.
      - **Selfie con CI**: Debe ser una foto REAL tomada en tiempo real, no una foto de una foto o una imagen compuesta. Verifica que la persona esté realmente sosteniendo el documento y que ambos (persona y documento) estén en la misma imagen real.

   b) **Verificar que la selfie con CI contiene AMBOS elementos simultáneamente:**
      - La selfie con CI DEBE contener claramente el ROSTRO de la persona Y el DOCUMENTO CI visible al mismo tiempo en la misma imagen.
      - La persona debe estar sosteniendo físicamente el documento (no debe ser una imagen compuesta o editada).
      - Ambos elementos deben ser claramente visibles y legibles en la misma foto.

   c) **Comparación de rostros entre selfie y CI:**
      - Compara el rostro en la selfie principal con el rostro en la foto del CI.
      - Analiza características faciales: forma del rostro, ojos, nariz, boca, estructura facial general, distancia entre ojos, forma de la mandíbula.
      - Determina si parecen ser la misma persona. Aunque no sea 100% preciso, busca similitudes razonables en las características faciales principales.

   d) **Comparación de rostros entre selfie principal y selfies del liveness:**
      - Compara el rostro en la selfie principal con los rostros en las 5 selfies del liveness detection.
      - TODAS las selfies del liveness deben parecer ser de la MISMA persona que la selfie principal.
      - Analiza características faciales consistentes entre todas las selfies (misma persona, diferentes poses).
      - Si alguna selfie del liveness no parece ser de la misma persona, es una señal de alerta.

   e) **Comparación de rostros entre selfie con CI y selfies anteriores:**
      - Compara el rostro en la selfie con CI con:
        * El rostro en la selfie principal
        * Los rostros en las selfies del liveness
      - El rostro en la selfie con CI debe parecer ser de la MISMA persona que aparece en todas las demás selfies.
      - Esta es una verificación crítica para asegurar que la persona que sostiene el documento es la misma que se tomó las selfies anteriores.

   f) **CI (Cédula)**: Debe ser un documento de identidad venezolano legible. Verifica que se pueda leer información básica (nombre, CI, fecha de nacimiento).

2. **Consistencia de nombres:**
   - El nombre completo del perfil debe ser coherente (no debe haber discrepancias obvias).
   - Si hay `kyc_document_number`, debe coincidir con el formato esperado.
   - Compara el nombre en los datos con el nombre visible en la CI (si es legible en la imagen).

3. **Coherencia de documentos:**
   - El CI (`ci_number`) debe tener formato válido venezolano (V-12345678).
   - El RIF (`ranch.tax_id`) debe tener formato válido (V-12345678-9 o J-12345678-9).
   - La fecha de nacimiento debe ser razonable (edad entre 18 y 100 años).

4. **Coherencia persona-negocio:**
   - Si el RIF es tipo "J" (jurídico), la razón social debe existir y ser coherente.
   - Si el RIF es tipo "V" (natural), debe haber coherencia entre el nombre del perfil y el nombre legal de la hacienda.

5. **Completitud:**
   - Deben existir todas las imágenes requeridas (CI, RIF, selfie, selfie con documento).

**Respuesta requerida:**

Debes responder EXCLUSIVAMENTE con un JSON válido, sin texto extra ni bloques markdown, con esta estructura:

{
  "decision": "verified" | "rejected" | "pending",
  "reasons": ["razón 1", "razón 2", ...],
  "confidence": "high" | "medium" | "low",
  "face_analysis": {
    "selfie_has_face": true/false,
    "selfie_with_doc_has_face": true/false,
    "selfie_with_doc_has_document": true/false,
    "faces_match": "yes" | "no" | "uncertain",
    "face_match_confidence": "high" | "medium" | "low"
  }
}

- **decision**: 
  - `verified`: Todo es consistente y válido, las imágenes son correctas, y el usuario puede ser verificado.
  - `rejected`: Hay inconsistencias graves, imágenes inválidas (foto de foto, pantalla, sin rostro, etc.), o rostros que claramente no coinciden.
  - `pending`: Hay dudas menores que requieren revisión manual (no usar a menos que sea realmente necesario).

- **reasons**: Lista de razones que justifican la decisión (máximo 3 razones, ser específico).

- **confidence**: Nivel de confianza en la decisión.

- **face_analysis**: Análisis facial detallado:
  - `selfie_has_face`: La selfie principal contiene un rostro visible
  - `selfie_is_real`: La selfie principal es una foto real (no foto de foto, pantalla, o dibujo)
  - `selfie_with_doc_has_face`: La selfie con CI contiene un rostro visible
  - `selfie_with_doc_has_document`: La selfie con CI contiene el documento visible
  - `selfie_with_doc_is_real`: La selfie con CI es una foto real tomada en tiempo real
  - `liveness_selfies_count`: Número de selfies del liveness proporcionadas (debe ser entre 1 y 5)
  - `liveness_selfies_are_real`: Todas las selfies del liveness son fotos reales
  - `liveness_selfies_match_main`: Las selfies del liveness parecen ser de la misma persona que la selfie principal
  - `selfie_with_doc_matches_main`: El rostro en la selfie con CI coincide con el rostro en la selfie principal
  - `selfie_with_doc_matches_liveness`: El rostro en la selfie con CI coincide con los rostros en las selfies del liveness
  - `ci_face_matches_selfie`: El rostro en el CI coincide con el rostro en la selfie principal
  - `all_faces_match`: Todos los rostros (selfie, CI, selfie con CI, liveness) parecen ser de la misma persona
  - `face_match_confidence`: Confianza general en la comparación de rostros ("high", "medium", "low")

**IMPORTANTE - Reglas estrictas de verificación:**
- **VERIFICAR**: Todas las selfies (principal, liveness, y con CI) deben ser fotos REALES, no fotos de fotos, pantallas, o dibujos.
- **VERIFICAR**: La selfie con CI debe contener AMBOS elementos (rostro y documento) claramente visibles en la misma imagen real.
- **VERIFICAR**: Todos los rostros deben parecer ser de la MISMA persona (selfie principal, CI, selfie con CI, y todas las selfies del liveness).
- **VERIFICAR**: Las selfies del liveness deben mostrar a la misma persona en diferentes poses (frontal, arriba, abajo, izquierda, derecha).

- **DECISIÓN "verified"**: Solo si:
  * Todas las imágenes son reales (no fotos de fotos)
  * La selfie con CI tiene ambos elementos (rostro y documento) claramente visibles
  * Todos los rostros parecen ser de la misma persona
  * Las selfies del liveness coinciden con la selfie principal
  * Los datos son consistentes y válidos

- **DECISIÓN "rejected"**: Si:
  * Alguna imagen no es real (foto de foto, pantalla, dibujo)
  * La selfie con CI no tiene ambos elementos o no es real
  * Los rostros claramente NO coinciden entre sí
  * Las selfies del liveness no parecen ser de la misma persona que la selfie principal
  * Hay inconsistencias graves en los datos

- Sé ESTRICTO: La seguridad es prioritaria. Si hay dudas sobre la autenticidad de las imágenes o la identidad de la persona, rechaza.
PROMPT;
    }

    /**
     * Llamar a la API de Gemini
     * @param string $prompt El prompt de texto
     * @param array $images Array de imágenes en formato base64 con mime_type
     */
    private function callGemini(string $prompt, array $images = []): ?array
    {
        $apiKey = config('services.google_gen_ai.api_key');
        $baseUrl = rtrim(config('services.google_gen_ai.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = ltrim(config('services.google_gen_ai.model', 'models/gemini-2.0-flash'), '/');

        $endpoint = sprintf('%s/%s:generateContent', $baseUrl, $model);

        // Construir partes del mensaje (texto + imágenes)
        $parts = [['text' => $prompt]];
        
        // Agregar imágenes si existen
        foreach ($images as $image) {
            // Extraer base64 sin el prefijo data:image/...;base64,
            $base64Data = preg_replace('/^data:[^;]+;base64,/', '', $image['data']);
            
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $image['mime_type'],
                    'data' => $base64Data,
                ],
            ];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $apiKey,
        ])->timeout(60)->post($endpoint, [ // Aumentar timeout a 60s para imágenes
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => $parts,
                ],
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE',
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2, // Más determinista para decisiones KYC
                'topK' => 32,
                'topP' => 0.8,
                'maxOutputTokens' => 512,
            ],
        ]);

        if (!$response->successful()) {
            Log::warning('KycEvaluationService: Gemini request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        $rawText = data_get($data, 'candidates.0.content.parts.0.text');

        if (!$rawText) {
            Log::warning('KycEvaluationService: Gemini no devolvió texto');
            return null;
        }

        // Log de la respuesta cruda de Gemini para debugging
        Log::info('KycEvaluationService: Respuesta cruda de Gemini', [
            'raw_text_preview' => substr($rawText, 0, 500), // Primeros 500 caracteres
            'raw_text_length' => strlen($rawText),
        ]);

        $decoded = $this->decodeGeminiJson($rawText);
        
        // Log de la respuesta decodificada completa
        if ($decoded) {
            Log::info('KycEvaluationService: Respuesta decodificada de Gemini', [
                'decision' => $decoded['decision'] ?? null,
                'reasons' => $decoded['reasons'] ?? null,
                'confidence' => $decoded['confidence'] ?? null,
                'face_analysis' => $decoded['face_analysis'] ?? null,
                'full_response' => $decoded, // Respuesta completa
            ]);
        }

        return $decoded;
    }

    /**
     * Decodificar JSON de respuesta de Gemini
     */
    private function decodeGeminiJson(string $rawText): ?array
    {
        $clean = trim($rawText);

        // Remover bloques markdown si existen
        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```[a-zA-Z]*\n?/', '', $clean);
            $clean = preg_replace('/```$/', '', $clean);
        }

        $clean = trim($clean);
        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('KycEvaluationService: Gemini JSON decode error', [
                'error' => json_last_error_msg(),
                'raw' => $clean,
            ]);
            return null;
        }

        return $decoded;
    }

    /**
     * Parsear respuesta de Gemini y convertir a formato interno
     */
    private function parseGeminiResponse(array $response): array
    {
        $decision = $response['decision'] ?? 'pending';
        $reasons = $response['reasons'] ?? [];
        $confidence = $response['confidence'] ?? 'medium';
        $faceAnalysis = $response['face_analysis'] ?? null;

        // Validar que la decisión sea válida
        if (!in_array($decision, ['verified', 'rejected', 'pending'])) {
            Log::warning('KycEvaluationService: Decisión inválida de Gemini', ['decision' => $decision]);
            $decision = 'pending';
        }

        // Validar análisis facial si existe
        if ($faceAnalysis !== null) {
            // Si la selfie no tiene rostro, rechazar
            if (isset($faceAnalysis['selfie_has_face']) && !$faceAnalysis['selfie_has_face']) {
                $decision = 'rejected';
                $reasons[] = 'La selfie no contiene un rostro visible.';
            }

            // Si la selfie no es real (foto de foto, pantalla, etc.), rechazar
            if (isset($faceAnalysis['selfie_is_real']) && !$faceAnalysis['selfie_is_real']) {
                $decision = 'rejected';
                $reasons[] = 'La selfie no parece ser una foto real (posible foto de foto, pantalla, o imagen procesada).';
            }

            // Si la selfie con CI no tiene ambos elementos, rechazar
            if (isset($faceAnalysis['selfie_with_doc_has_face']) && !$faceAnalysis['selfie_with_doc_has_face']) {
                $decision = 'rejected';
                $reasons[] = 'La selfie con CI no contiene un rostro visible.';
            }
            if (isset($faceAnalysis['selfie_with_doc_has_document']) && !$faceAnalysis['selfie_with_doc_has_document']) {
                $decision = 'rejected';
                $reasons[] = 'La selfie con CI no contiene el documento visible.';
            }

            // Si la selfie con CI no es real, rechazar
            if (isset($faceAnalysis['selfie_with_doc_is_real']) && !$faceAnalysis['selfie_with_doc_is_real']) {
                $decision = 'rejected';
                $reasons[] = 'La selfie con CI no parece ser una foto real tomada en tiempo real.';
            }

            // Si las selfies del liveness no son reales, rechazar
            if (isset($faceAnalysis['liveness_selfies_are_real']) && !$faceAnalysis['liveness_selfies_are_real']) {
                $decision = 'rejected';
                $reasons[] = 'Las selfies del liveness detection no parecen ser fotos reales.';
            }

            // Si las selfies del liveness no coinciden con la selfie principal, rechazar
            if (isset($faceAnalysis['liveness_selfies_match_main']) && $faceAnalysis['liveness_selfies_match_main'] === false) {
                $decision = 'rejected';
                $reasons[] = 'Las selfies del liveness detection no parecen ser de la misma persona que la selfie principal.';
            }

            // Si la selfie con CI no coincide con la selfie principal, rechazar
            if (isset($faceAnalysis['selfie_with_doc_matches_main']) && $faceAnalysis['selfie_with_doc_matches_main'] === false) {
                $decision = 'rejected';
                $reasons[] = 'El rostro en la selfie con CI no coincide con el rostro en la selfie principal.';
            }

            // Si la selfie con CI no coincide con las selfies del liveness, rechazar
            if (isset($faceAnalysis['selfie_with_doc_matches_liveness']) && $faceAnalysis['selfie_with_doc_matches_liveness'] === false) {
                $decision = 'rejected';
                $reasons[] = 'El rostro en la selfie con CI no coincide con los rostros en las selfies del liveness.';
            }

            // Si el rostro del CI no coincide con la selfie, rechazar
            if (isset($faceAnalysis['ci_face_matches_selfie']) && $faceAnalysis['ci_face_matches_selfie'] === false) {
                $decision = 'rejected';
                $reasons[] = 'El rostro en el CI no coincide con el rostro en la selfie principal.';
            }

            // Si no todos los rostros coinciden, rechazar
            if (isset($faceAnalysis['all_faces_match']) && $faceAnalysis['all_faces_match'] === false) {
                $decision = 'rejected';
                $reasons[] = 'No todos los rostros (selfie, CI, selfie con CI, liveness) parecen ser de la misma persona.';
            }

            // Validación legacy (mantener compatibilidad)
            if (isset($faceAnalysis['faces_match']) && $faceAnalysis['faces_match'] === 'no') {
                $decision = 'rejected';
                $reasons[] = 'Los rostros en la selfie y el CI no parecen ser la misma persona.';
            }
        }

        // Convertir array de razones a string
        $reasonText = !empty($reasons) ? implode(' ', $reasons) : null;

        // Si la confianza es baja y la decisión es verified, cambiar a pending
        if ($confidence === 'low' && $decision === 'verified') {
            $decision = 'pending';
            $reasonText = 'La evaluación automática tiene baja confianza. Se requiere revisión manual.';
        }

        // Log del análisis facial para debugging
        if ($faceAnalysis !== null) {
            Log::info('KycEvaluationService: Análisis facial de Gemini', [
                'face_analysis' => $faceAnalysis,
                'final_decision' => $decision,
            ]);
        }

        return [
            'status' => $decision,
            'reason' => $reasonText,
            'confidence' => $confidence,
            'face_analysis' => $faceAnalysis,
        ];
    }
}


