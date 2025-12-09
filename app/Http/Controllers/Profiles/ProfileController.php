<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Listar todos los perfiles.
     */
    public function index()
    {
        $profiles = Profile::with(['user', 'addresses'])->get();
        return response()->json($profiles);
    }

    /**
     * Crear un nuevo perfil.
     */
    public function store(Request $request)
    {
        // Verificar si ya existe un perfil para el usuario ANTES de validar
        $existingProfile = Profile::where('user_id', $request->user_id)->first();
        
        // Validación de los datos de entrada.
        // Si existe un perfil, ignorarlo en la validación de ci_number único
        $ciNumberRule = 'required|string|max:20';
        if ($existingProfile) {
            $ciNumberRule .= '|unique:profiles,ci_number,' . $existingProfile->id;
        } else {
            $ciNumberRule .= '|unique:profiles,ci_number';
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'required|string|max:255',
            'secondLastName' => 'nullable|string|max:255',
            'photo_users' => 'nullable|image|mimes:jpeg,png,jpg',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'nullable|in:married,divorced,single',
            'sex' => 'nullable|in:F,M',
            'ci_number' => $ciNumberRule,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'message' => 'Error de validación. Por favor, revise los datos ingresados.'
            ], 400);
        }

        if ($existingProfile) {
            // Si el perfil existe, actualizarlo con los nuevos datos
            $profileData = $request->only([
                'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex', 'ci_number'
            ]);

            // Establecer valores predeterminados para campos opcionales.
            $profileData['middleName'] = $request->middleName ?? $existingProfile->middleName ?? '';
            $profileData['secondLastName'] = $request->secondLastName ?? $existingProfile->secondLastName ?? '';

            // Manejar la carga de la imagen si se proporciona.
            if ($request->hasFile('photo_users')) {
                // Obtener la URL base según el entorno.
                $baseUrl = env('APP_ENV') === 'production'
                    ? env('APP_URL_PRODUCTION')
                    : env('APP_URL_LOCAL');

                // Guardar la nueva imagen en el disco público.
                $path = $request->file('photo_users')->store('profile_images', 'public');
                $profileData['photo_users'] = $baseUrl . '/storage/' . $path; // Guarda la URL pública.
            }

            // Actualizar el perfil existente.
            $existingProfile->update($profileData);

            return response()->json([
                'message' => 'Perfil actualizado exitosamente.',
                'profile' => $existingProfile->fresh()
            ], 200); // Código de estado HTTP 200: OK
        }



        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex', 'ci_number'
        ]);

          // Establecer valores predeterminados para campos opcionales.
        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified'; // Estado inicial.

        // Manejar la carga de la imagen.
        if ($request->hasFile('photo_users')) {
            // Obtener la URL base según el entorno.
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');

            // Guardar la nueva imagen en el disco público.
            $path = $request->file('photo_users')->store('profile_images', 'public');
            $profileData['photo_users'] = $baseUrl . '/storage/' . $path; // Guarda la URL pública.
        }

        // Crear el perfil.
        $profile = Profile::create($profileData);

        return response()->json([
            'message' => 'Perfil creado exitosamente.',
            'profile' => $profile
        ], 201);
    }

    /**
     * Obtener el perfil del usuario autenticado (para /api/profile sin ID).
     */
    public function getMyProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $profile = Profile::with([
            'user',
            'ranches.address.city.state.country', // Cargar direcciones completas de ranches
            'addresses'
        ])
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        return response()->json($profile);
    }

    /**
     * Actualizar el perfil del usuario autenticado (para /api/profile sin ID).
     */
    public function updateMyProfile(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $profile = Profile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        // Log para debugging
        Log::info('updateMyProfile iniciado', [
            'has_file' => $request->hasFile('photo_users'),
            'all_files' => $request->allFiles(),
            'method' => $request->method(),
        ]);

        // Validar los datos recibidos
        $validatedData = $request->validate([
            'firstName' => 'sometimes|required|string|max:255',
            'middleName' => 'nullable|string|max:255',
            'lastName' => 'sometimes|required|string|max:255',
            'secondLastName' => 'nullable|string|max:255',
            'photo_users' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            'bio' => 'nullable|string|max:500',
            'date_of_birth' => 'sometimes|required|date',
            'maritalStatus' => 'nullable|in:married,divorced,single',
            'sex' => 'nullable|in:F,M',
            'ci_number' => 'sometimes|required|string|max:20',
            'accepts_calls' => 'nullable|boolean',
            'accepts_whatsapp' => 'nullable|boolean',
            'accepts_emails' => 'nullable|boolean',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        // Manejar la carga de la imagen si se proporciona
        if ($request->hasFile('photo_users')) {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');

            $path = $request->file('photo_users')->store('profile_images', 'public');
            $validatedData['photo_users'] = $baseUrl . '/storage/' . $path;
            
            Log::info('Foto de perfil procesada', [
                'path' => $path,
                'url' => $validatedData['photo_users'],
            ]);
        }

        // Actualizar el perfil
        $profile->update($validatedData);

        Log::info('Perfil actualizado', [
            'photo_users_en_bd' => $profile->fresh()->photo_users,
        ]);

        // Retornar el perfil actualizado con relaciones
        $updatedProfile = Profile::with(['user', 'ranches', 'addresses'])
            ->find($profile->id);

        return response()->json($updatedProfile);
    }

    /**
     * Subir/actualizar foto de perfil (POST dedicado para multipart)
     */
    public function uploadPhoto(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $profile = Profile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        // Log para debugging
        Log::info('uploadPhoto iniciado', [
            'has_file' => $request->hasFile('photo_users'),
            'all_files' => $request->allFiles(),
        ]);

        // Validar solo la foto
        $request->validate([
            'photo_users' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        $baseUrl = env('APP_ENV') === 'production'
            ? env('APP_URL_PRODUCTION')
            : env('APP_URL_LOCAL');

        // Eliminar foto anterior si existe
        if ($profile->photo_users) {
            $oldPath = str_replace($baseUrl . '/storage/', '', $profile->photo_users);
            Storage::disk('public')->delete($oldPath);
        }

        // Guardar nueva foto
        $path = $request->file('photo_users')->store('profile_images', 'public');
        $photoUrl = $baseUrl . '/storage/' . $path;

        // Actualizar perfil
        $profile->update(['photo_users' => $photoUrl]);

        Log::info('Foto actualizada', [
            'path' => $path,
            'url' => $photoUrl,
            'foto_en_bd' => $profile->fresh()->photo_users,
        ]);

        // Retornar perfil actualizado
        $updatedProfile = Profile::with(['user', 'ranches', 'addresses'])
            ->find($profile->id);

        return response()->json($updatedProfile);
    }

    /**
     * Mostrar un perfil específico por ID (perfil público).
     */
    public function show($id)
    {
        $profile = Profile::with(['user', 'ranches', 'addresses'])->find($id);

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        return response()->json($profile);
    }

    /**
     * Verificar completitud del perfil y hacienda principal para publicar productos
     * GET /api/me/completeness
     */
    public function checkCompleteness(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $profile = Profile::with(['ranches.address', 'addresses'])
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return response()->json([
                'profile_complete' => false,
                'ranch_complete' => false,
                'can_publish' => false,
                'missing_profile_fields' => ['profile'],
                'missing_ranch_fields' => ['ranch'],
                'message' => 'Perfil no encontrado'
            ], 404);
        }

        // Validar campos obligatorios del Profile
        $missingProfileFields = [];
        
        if (empty($profile->firstName)) $missingProfileFields[] = 'firstName';
        if (empty($profile->middleName)) $missingProfileFields[] = 'middleName';
        if (empty($profile->lastName)) $missingProfileFields[] = 'lastName';
        if (empty($profile->secondLastName)) $missingProfileFields[] = 'secondLastName';
        if (empty($profile->date_of_birth)) $missingProfileFields[] = 'date_of_birth';
        if (empty($profile->ci_number)) $missingProfileFields[] = 'ci_number';
        if (empty($profile->sex)) $missingProfileFields[] = 'sex';
        if (empty($profile->user_type)) $missingProfileFields[] = 'user_type';
        if (empty($profile->photo_users)) $missingProfileFields[] = 'photo_users';
        // Bio es opcional según el modelo, pero podemos requerirla si quieres
        // if (empty($profile->bio)) $missingProfileFields[] = 'bio';

        $profileComplete = empty($missingProfileFields);

        // Validar Ranch principal
        $primaryRanch = $profile->ranches()->where('is_primary', true)->first();
        $missingRanchFields = [];
        $ranchComplete = false;

        if (!$primaryRanch) {
            $missingRanchFields[] = 'ranch'; // No existe hacienda principal
        } else {
            // Validar campos obligatorios del Ranch
            if (empty($primaryRanch->name)) $missingRanchFields[] = 'name';
            if (empty($primaryRanch->address_id)) {
                $missingRanchFields[] = 'address';
            } else {
                // Validar que la dirección tenga datos completos
                $address = $primaryRanch->address;
                if (!$address) {
                    $missingRanchFields[] = 'address';
                } else {
                    if (empty($address->city_id)) $missingRanchFields[] = 'address.city';
                    if (empty($address->adressses)) $missingRanchFields[] = 'address.adressses';
                }
            }
            // business_description es opcional según el modelo
            // contact_hours es opcional según el modelo

            $ranchComplete = empty($missingRanchFields);
        }

        $canPublish = $profileComplete && $ranchComplete;

        return response()->json([
            'profile_complete' => $profileComplete,
            'ranch_complete' => $ranchComplete,
            'can_publish' => $canPublish,
            'missing_profile_fields' => $missingProfileFields,
            'missing_ranch_fields' => $missingRanchFields,
            'message' => $canPublish 
                ? 'Perfil y hacienda completos. Puedes publicar productos.'
                : 'Debes completar tu perfil y/o hacienda antes de publicar productos.'
        ]);
    }
    
    public function update(Request $request, $id)
{
    // Buscar el perfil por ID o devolver error 404.
    $profile = Profile::findOrFail($id);

    // Validar los datos recibidos, incluyendo el formato correcto para la fecha.
    $validatedData = $request->validate([
        'firstName' => 'required|string|max:255',
        'middleName' => 'nullable|string|max:255',
        'lastName' => 'required|string|max:255',
        'secondLastName' => 'nullable|string|max:255',
        'photo_users' => 'nullable|image|mimes:jpeg,png,jpg',
        'date_of_birth' => 'required|date',
        'maritalStatus' => 'required|in:married,divorced,single',
        'sex' => 'required|in:F,M',
    ]);



    // Log para depurar la fecha recibida y asegurar que esté en el formato correcto
    Log::info('Fecha recibida: ' . $validatedData['date_of_birth']);  // Verificar que esté en formato Y-m-d

    // Obtener el nombre del perfil y la fecha de creación
    $created_at = $profile->created_at->format('YmdHis');  // Formato de fecha
    $date_of_birth = Carbon::parse($validatedData['date_of_birth'])->format('Ymd'); // Formato de fecha de nacimiento (Ymd)
    $firstName = $validatedData['firstName'];
    $lastName = $validatedData['lastName'];
    $randomDigits = strtoupper(substr(md5(mt_rand()), 0, 7));  // Generar 7 caracteres aleatorios

    // Establecer valores predeterminados para campos opcionales
    $validatedData['middleName'] = $request->middleName ?? '';  // Asegurar que 'middleName' no sea null
    $validatedData['secondLastName'] = $request->secondLastName ?? '';  // Asegurar que 'secondLastName' no sea null


    // Crear el nuevo nombre de la imagen
    $newImageName = "photo_users-{$created_at}-{$date_of_birth}-{$firstName}-{$lastName}-{$randomDigits}.jpg";

    // Obtener la URL base según el entorno
    $baseUrl = env('APP_ENV') === 'production'
        ? env('APP_URL_PRODUCTION')
        : env('APP_URL_LOCAL');

    // Mantener la URL de la foto anterior (si existe)
    $photo_usersxxx = $profile->photo_users;

    // Actualizar los campos del perfil
    $profile->fill($validatedData);

    // Manejo del archivo (si se sube uno nuevo)
    if ($request->hasFile('photo_users')) {
        // Eliminar la imagen anterior si existe
        if ($profile->photo_users) {
            // Log de la imagen anterior desde la base de datos
            Storage::disk('public')->delete(str_replace($baseUrl . '/storage/', '', $photo_usersxxx));
        } else {
            Log::info('No hay imagen anterior para eliminar.');
        }

        // Guardar la nueva imagen en el disco público
        $path = $request->file('photo_users')->storeAs('profile_images', $newImageName, 'public');
        $profile->photo_users = $baseUrl . '/storage/' . $path;
    }

    // Guardar los cambios en el perfil
    $profile->save();

    return response()->json([
        'message' => 'Perfil actualizado exitosamente.',
        'profile' => $profile,
        'isSuccess' => true
    ], 200);
}


    /**
     * Eliminar un perfil.
     */
    public function destroy($id)
    {
        $profile = Profile::find($id);

        if (!$profile) {
            return response()->json(['message' => 'Perfil no encontrado'], 404);
        }

        // Eliminar la imagen asociada si existe.
        if ($profile->photo_users) {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');
            Storage::disk('public')->delete(str_replace($baseUrl . '/storage/', '', $profile->photo_users));
        }

        $profile->delete();

        return response()->json(['message' => 'Perfil eliminado exitosamente']);
    }


// En tu controlador (UserController)
        public function getProfileId($id)
        {

            $profile = Profile::where('user_id', $id)->first();
            if ($profile) {
                return response()->json(['profileId' => $profile->id], 200);
            } else {
                return response()->json(['error' => 'User profile not found'], 404);
            }
        }

    /**
     * Crear un perfil de delivery agent.
     */
    public function createDeliveryAgent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
            'vehicle_type' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'company_id' => 'nullable|exists:delivery_companies,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si ya existe un perfil para el usuario
        $existingProfile = Profile::where('user_id', $request->user_id)->first();

        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409);
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);

        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified';

        // Crear el perfil
        $profile = Profile::create($profileData);

        // Crear el delivery agent asociado
        $deliveryAgentData = [
            'profile_id' => $profile->id,
            'vehicle_type' => $request->vehicle_type,
            'phone' => $request->phone,
            'status' => 'activo',
            'working' => false,
        ];

        // Si se proporciona company_id, agregarlo
        if ($request->has('company_id') && $request->company_id) {
            $deliveryAgentData['company_id'] = $request->company_id;
        }

        $deliveryAgent = \App\Models\DeliveryAgent::create($deliveryAgentData);

        return response()->json([
            'success' => true,
            'message' => 'Delivery agent profile created successfully',
            'data' => [
                'profile' => $profile,
                'delivery_agent' => $deliveryAgent
            ]
        ], 201);
    }

    /**
     * Crear un perfil de commerce.
     */
    public function createCommerce(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
            'business_name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'is_open' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si ya existe un perfil para el usuario
        $existingProfile = Profile::where('user_id', $request->user_id)->first();
        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409);
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);
        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified';

        // Crear el perfil
        $profile = Profile::create($profileData);

        // Crear el commerce asociado
        $commerce = \App\Models\Commerce::create([
            'profile_id' => $profile->id,
            'business_name' => $request->business_name,
            'description' => $request->description,
            'address' => $request->address,
            'phone' => $request->phone,
            'open' => $request->is_open,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $commerce->id,
                'business_name' => $commerce->business_name,
                'description' => $commerce->description,
                'address' => $commerce->address,
                'phone' => $commerce->phone,
                'open' => $commerce->open,
                'mobile_payment_id' => null, // Agregado para el test
                'mobile_payment_bank' => null, // Agregado para el test
                'mobile_payment_phone' => null // Agregado para el test
            ]
        ], 201);
    }

    /**
     * Crear un perfil de delivery company.
     */
    public function createDeliveryCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'maritalStatus' => 'required|in:married,divorced,single',
            'sex' => 'required|in:F,M',
            'company_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Verificar si ya existe un perfil para el usuario
        $existingProfile = Profile::where('user_id', $request->user_id)->first();

        if ($existingProfile) {
            return response()->json([
                'message' => 'Ya existe un perfil asociado a este usuario.',
                'profile' => $existingProfile
            ], 409);
        }

        $profileData = $request->only([
            'user_id', 'firstName', 'lastName', 'date_of_birth', 'maritalStatus', 'sex'
        ]);

        $profileData['middleName'] = $request->middleName ?? '';
        $profileData['secondLastName'] = $request->secondLastName ?? '';
        $profileData['status'] = 'notverified';

        // Crear el perfil
        $profile = Profile::create($profileData);

        // Crear la delivery company asociada
        $deliveryCompany = \App\Models\DeliveryCompany::create([
            'profile_id' => $profile->id,
            'name' => $request->company_name,
            'tax_id' => $request->ci ?? '00000000000',
            'phone' => $request->phone,
            'address' => $request->address,
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Delivery company profile created successfully',
            'data' => [
                'profile' => $profile,
                'delivery_company' => $deliveryCompany
            ]
        ], 201);
    }

    /**
     * Obtener métricas del usuario autenticado
     * GET /api/me/metrics
     */
    public function myMetrics(Request $request)
    {
        $user = $request->user();
        
        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Usuario no tiene perfil'], 404);
        }

        $profileId = $user->profile->id;

        // Obtener ranches del usuario
        $ranches = \App\Models\Ranch::where('profile_id', $profileId)->pluck('id');

        // Obtener productos de esos ranches
        $products = \App\Models\Product::whereIn('ranch_id', $ranches)->get();

        // Calcular métricas
        $metrics = [
            'total_products' => $products->count(),
            'active_products' => $products->where('status', 'active')->count(),
            'sold_products' => $products->where('status', 'sold')->count(),
            'total_views' => $products->sum('views_count'),
            'total_favorites' => \DB::table('favorites')
                ->whereIn('product_id', $products->pluck('id'))
                ->count(),
            'total_ranches' => $ranches->count(),
            'profile_rating' => (float) $user->profile->rating,
            'profile_ratings_count' => $user->profile->ratings_count,
        ];

        return response()->json($metrics);
    }
}
