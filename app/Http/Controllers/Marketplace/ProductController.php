<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Product;
use App\Models\Ranch;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductController extends BaseController
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['ranch.address.city.state.country', 'images'])
            ->when($request->filled('state_id'), fn($q) => $q->where('state_id', (int) $request->input('state_id'))) // ✅ NUEVO: filtro por estado
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('breed'), fn($q) => $q->where('breed', $request->string('breed')))
            ->when($request->filled('sex'), fn($q) => $q->where('sex', $request->string('sex')))
            ->when($request->filled('purpose'), fn($q) => $q->where('purpose', $request->string('purpose')))
            ->when($request->filled('is_vaccinated'), fn($q) => $q->where('is_vaccinated', filter_var($request->input('is_vaccinated'), FILTER_VALIDATE_BOOLEAN)))
            ->when($request->filled('min_price'), fn($q) => $q->where('price', '>=', (float) $request->input('min_price')))
            ->when($request->filled('max_price'), fn($q) => $q->where('price', '<=', (float) $request->input('max_price')))
            ->when($request->filled('search'), fn($q) => $q->where(function($query) use ($request) {
                $search = $request->string('search');
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('breed', 'like', "%{$search}%");
            }))
            ->when($request->filled('quantity'), fn($q) => $q->where('quantity', '>=', (int) $request->input('quantity')))
            ->when($request->filled('sort_by'), function($q) use ($request) {
                $sortBy = $request->string('sort_by');
                switch($sortBy) {
                    case 'price_asc':
                        return $q->orderBy('price', 'asc');
                    case 'price_desc':
                        return $q->orderBy('price', 'desc');
                    case 'newest':
                    default:
                        return $q->orderBy('created_at', 'desc');
                }
            })
            ->when(! $request->filled('status'), fn($q) => $q->where('status', 'active'))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->string('status')))
            ->when(! $request->filled('sort_by'), fn($q) => $q->orderByDesc('created_at'));

        $perPage = (int) $request->input('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function show(Product $product)
    {
        $product->load(['ranch.address.city.state.country', 'images', 'categories']);

        $user = Auth::user();
        if (! $user || optional($product->ranch)->profile_id !== optional($user->profile)->id) {
            $product->incrementViews();
        }

        // ✅ Detectar si la petición viene de un navegador (no de la app)
        $userAgent = request()->header('User-Agent', '');
        $isBrowser = !str_contains(strtolower($userAgent), 'okhttp') && 
                     !str_contains(strtolower($userAgent), 'dart') &&
                     !request()->expectsJson();
        
        if ($isBrowser) {
            // ✅ Mostrar página HTML amigable en lugar de JSON
            return $this->showProductLandingPage($product);
        }

        return response()->json($product);
    }

    /**
     * Mostrar página de aterrizaje para compartir productos
     */
    private function showProductLandingPage(Product $product)
    {
        $title = $product->title ?? 'Ganado en Venta';
        $description = $product->description ?? 'Ver más detalles en la app CorralX';
        $image = $product->images->where('is_primary', true)->first()?->file_url 
                 ?? $product->images->first()?->file_url
                 ?? asset('images/default-product.png');
        $price = $product->price ?? 0;
        $currency = $product->currency ?? 'USD';
        
        // Play Store URL (a actualizar cuando publiquen)
        $playStoreUrl = 'https://play.google.com/store/apps/details?id=com.example.zonix';
        
        // Deep link para abrir en la app
        $deepLink = "corralx://product/{$product->id}";
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - CorralX</title>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="product">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$description}">
    <meta property="og:image" content="{$image}">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:title" content="{$title}">
    <meta property="twitter:description" content="{$description}">
    <meta property="twitter:image" content="{$image}">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #386A20 0%, #2a4f17 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .image-container {
            width: 100%;
            height: 300px;
            overflow: hidden;
            background: #f0f0f0;
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .content {
            padding: 30px;
        }
        h1 {
            font-size: 24px;
            color: #1A1C18;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .price {
            font-size: 28px;
            font-weight: bold;
            color: #386A20;
            margin-bottom: 15px;
        }
        .description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 15px;
        }
        .buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            padding: 16px;
            border-radius: 12px;
            text-decoration: none;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
            display: block;
        }
        .btn:active {
            transform: scale(0.98);
        }
        .btn-primary {
            background: #386A20;
            color: white;
        }
        .btn-secondary {
            background: #E0E4D7;
            color: #386A20;
        }
        .app-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
        }
        .app-logo {
            width: 40px;
            height: 40px;
            background: #386A20;
            border-radius: 8px;
        }
        .app-name {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="image-container">
            <img src="{$image}" alt="{$title}" onerror="this.src='https://via.placeholder.com/500x300?text=CorralX'">
        </div>
        <div class="content">
            <h1>{$title}</h1>
            <div class="price">\${$price} {$currency}</div>
            <div class="description">{$description}</div>
            
            <div class="buttons">
                <a href="{$deepLink}" class="btn btn-primary" id="openApp">
                    Abrir en CorralX
                </a>
                <a href="{$playStoreUrl}" class="btn btn-secondary" target="_blank">
                    Descargar App Gratis
                </a>
            </div>
            
            <div class="app-badge">
                <div class="app-logo"></div>
                <div class="app-name">CorralX - Marketplace Ganadero</div>
            </div>
        </div>
    </div>
    
    <script>
        // Intentar abrir la app primero
        document.getElementById('openApp').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '{$deepLink}';
            
            // Si después de 2 segundos no se abrió la app, mostrar botón de descarga
            setTimeout(function() {
                // Detectar si estamos en Android o iOS
                var userAgent = navigator.userAgent || navigator.vendor || window.opera;
                if (/android/i.test(userAgent)) {
                    window.location.href = '{$playStoreUrl}';
                } else if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                    // iOS - App Store URL cuando publiquen
                    window.location.href = 'https://apps.apple.com/app/corralx';
                }
            }, 2000);
        });
    </script>
</body>
</html>
HTML;
        
        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function store(Request $request)
    {
        // ✅ Validar que el perfil y hacienda estén completos antes de permitir publicar
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        $profile = Profile::with(['ranches.address'])
            ->where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Debes completar tu perfil antes de publicar productos.',
                'error' => 'profile_incomplete'
            ], 422);
        }

        // Validar campos obligatorios del Profile
        $missingProfileFields = [];
        if (empty($profile->firstName)) $missingProfileFields[] = 'firstName';
        if (empty($profile->lastName)) $missingProfileFields[] = 'lastName';
        if (empty($profile->date_of_birth)) $missingProfileFields[] = 'date_of_birth';
        if (empty($profile->ci_number)) $missingProfileFields[] = 'ci_number';
        if (empty($profile->sex)) $missingProfileFields[] = 'sex';
        if (empty($profile->user_type)) $missingProfileFields[] = 'user_type';
        if (empty($profile->photo_users)) $missingProfileFields[] = 'photo_users';

        if (!empty($missingProfileFields)) {
            return response()->json([
                'message' => 'Debes completar tu perfil antes de publicar productos.',
                'error' => 'profile_incomplete',
                'missing_fields' => $missingProfileFields
            ], 422);
        }

        // Validar Ranch principal
        $primaryRanch = $profile->ranches()->where('is_primary', true)->first();
        if (!$primaryRanch) {
            return response()->json([
                'message' => 'Debes crear y completar una hacienda principal antes de publicar productos.',
                'error' => 'ranch_incomplete',
                'missing_fields' => ['ranch']
            ], 422);
        }

        $missingRanchFields = [];
        if (empty($primaryRanch->name)) $missingRanchFields[] = 'name';
        if (empty($primaryRanch->address_id)) {
            $missingRanchFields[] = 'address';
            } else {
                $address = $primaryRanch->address;
                if (!$address) {
                    $missingRanchFields[] = 'address';
                } else {
                    if (empty($address->city_id)) $missingRanchFields[] = 'address.city';
                    if (empty($address->adressses)) $missingRanchFields[] = 'address.adressses';
                }
            }

        if (!empty($missingRanchFields)) {
            return response()->json([
                'message' => 'Debes completar los datos de tu hacienda principal antes de publicar productos.',
                'error' => 'ranch_incomplete',
                'missing_fields' => $missingRanchFields
            ], 422);
        }

        // ✅ Validar KYC: el perfil debe tener kyc_status = verified
        if (method_exists($profile, 'isKycVerified') && ! $profile->isKycVerified()) {
            return response()->json([
                'message' => 'Debes completar la verificación de identidad (KYC) antes de publicar productos.',
                'error' => 'kyc_incomplete',
                'kyc_status' => $profile->kyc_status ?? 'no_verified',
            ], 422);
        }

        $breedEnum = [
            'Brahman','Holstein','Guzerat','Gyr','Nelore','Jersey','Angus','Simmental','Pardo Suizo','Charolais','Limousin','Santa Gertrudis','Brangus','Girolando','Carora','Criollo Limonero','Mosaico Perijanero','Indubrasil','Sardo Negro','Senepol','Romosinuano','Sahiwal','Búfalo Murrah','Búfalo Jafarabadi','Búfalo Mediterráneo','Búfalo Carabao','Búfalo Nili-Ravi','Búfalo Surti','Búfalo Pandharpuri','Búfalo Nagpuri','Búfalo Mehsana','Búfalo Bhadawari','Búfalo Toda','Búfalo Kundi','Búfalo Nili','Búfalo Ravi','Otra'
        ];
        $this->validate($request, [
            'ranch_id' => ['required', 'exists:ranches,id'],
            'state_id' => ['nullable', 'exists:states,id'], // ✅ NUEVO: state_id opcional
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            // 'type' eliminado - ahora se usa solo 'purpose'
            'breed' => ['required', Rule::in($breedEnum)],
            'age' => ['nullable', 'integer', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', Rule::in(['USD','VES'])],
            'weight_avg' => ['nullable', 'numeric', 'min:0'],
            'weight_min' => ['nullable', 'numeric', 'min:0'],
            'weight_max' => ['nullable', 'numeric', 'min:0'],
            'sex' => ['nullable', Rule::in(['male','female','mixed'])],
            'purpose' => ['required', Rule::in(['breeding','meat','dairy','mixed'])], // ✅ Ahora es obligatorio
            'feeding_type' => ['required', Rule::in(['pastura_natural','pasto_corte','concentrado','mixto','otro'])], // ✅ NUEVO: obligatorio
            'is_vaccinated' => ['boolean'],
            'delivery_method' => ['required', Rule::in(['pickup','delivery','both'])],
            'delivery_cost' => ['nullable', 'numeric', 'min:0'],
            'delivery_radius_km' => ['nullable', 'integer', 'min:0'],
            // 'negotiable' e 'is_featured' se eliminan del formulario, se guardan como false por defecto
        ]);

        // Ownership: el rancho debe pertenecer al perfil del usuario autenticado
        $authProfileId = optional(Auth::user()->profile)->id;
        $ranch = Ranch::findOrFail((int) $request->input('ranch_id'));
        // Con casts en modelos, podemos usar comparación estricta
        if ($ranch->profile_id !== $authProfileId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->only([
            'ranch_id','state_id','title','description','breed','age','quantity','price','currency','weight_avg','weight_min','weight_max','sex','purpose','feeding_type','health_certificate_url','vaccines_applied','last_vaccination','is_vaccinated','feeding_info','handling_info','origin_farm','available_from','available_until','delivery_method','delivery_cost','delivery_radius_km','price_type','min_order_quantity','transportation_included','documentation_included','genetic_tests_available','genetic_test_results','bloodline'
        ]);
        
        // Establecer valores por defecto para campos eliminados del formulario
        $data['type'] = 'engorde'; // Valor por defecto para compatibilidad
        $data['negotiable'] = false;
        $data['is_featured'] = false;

        // Convertir arrays a JSON si vienen como array
        if (isset($data['documentation_included']) && is_array($data['documentation_included'])) {
            $data['documentation_included'] = json_encode($data['documentation_included']);
        }
        if (isset($data['vaccines_applied']) && is_array($data['vaccines_applied'])) {
            $data['vaccines_applied'] = json_encode($data['vaccines_applied']);
        }
        if (isset($data['genetic_test_results']) && is_array($data['genetic_test_results'])) {
            $data['genetic_test_results'] = json_encode($data['genetic_test_results']);
        }

        $data['status'] = $data['status'] ?? 'active';
        $data['views'] = 0;

        $product = Product::create($data);

        return response()->json($product->fresh(['ranch.address.city.state.country','images']), 201);
    }

    public function update(Request $request, Product $product)
    {
        $breedEnum = [
            'Brahman','Holstein','Guzerat','Gyr','Nelore','Jersey','Angus','Simmental','Pardo Suizo','Charolais','Limousin','Santa Gertrudis','Brangus','Girolando','Carora','Criollo Limonero','Mosaico Perijanero','Indubrasil','Sardo Negro','Senepol','Romosinuano','Sahiwal','Búfalo Murrah','Búfalo Jafarabadi','Búfalo Mediterráneo','Búfalo Carabao','Búfalo Nili-Ravi','Búfalo Surti','Búfalo Pandharpuri','Búfalo Nagpuri','Búfalo Mehsana','Búfalo Bhadawari','Búfalo Toda','Búfalo Kundi','Búfalo Nili','Búfalo Ravi','Otra'
        ];
        $this->validate($request, [
            'title' => ['sometimes','string','max:255'],
            'description' => ['sometimes','string'],
            // 'type' eliminado - ahora se usa solo 'purpose'
            'breed' => ['sometimes', Rule::in($breedEnum)],
            'age' => ['nullable','integer','min:0'],
            'quantity' => ['sometimes','integer','min:1'],
            'price' => ['sometimes','numeric','min:0'],
            'currency' => ['sometimes', Rule::in(['USD','VES'])],
            'weight_avg' => ['nullable','numeric','min:0'],
            'weight_min' => ['nullable','numeric','min:0'],
            'weight_max' => ['nullable','numeric','min:0'],
            'sex' => ['nullable', Rule::in(['male','female','mixed'])],
            'purpose' => ['sometimes', Rule::in(['breeding','meat','dairy','mixed'])],
            'feeding_type' => ['sometimes', Rule::in(['pastura_natural','pasto_corte','concentrado','mixto','otro'])], // ✅ NUEVO
            'status' => ['sometimes', Rule::in(['active','paused','sold','expired'])],
            'is_vaccinated' => ['boolean'],
            'delivery_method' => ['sometimes', Rule::in(['pickup','delivery','both'])],
            'delivery_cost' => ['nullable','numeric','min:0'],
            'delivery_radius_km' => ['nullable','integer','min:0'],
            // 'negotiable' e 'is_featured' se eliminan del formulario
        ]);

        // Ownership: el producto debe pertenecer al perfil del usuario autenticado
        $authProfileId = optional(Auth::user()->profile)->id;
        if (optional($product->ranch)->profile_id !== $authProfileId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product->update($request->all());

        return response()->json($product->fresh(['ranch.address.city.state.country','images']));
    }

    public function destroy(Product $product)
    {
        // Ownership: el producto debe pertenecer al perfil del usuario autenticado
        $authProfileId = optional(Auth::user()->profile)->id;
        if (optional($product->ranch)->profile_id !== $authProfileId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $product->delete();
        return response()->json(['deleted' => true]);
    }

    /**
     * Obtener los productos del usuario autenticado
     * GET /api/me/products
     */
    public function myProducts(Request $request)
    {
        $user = $request->user();
        
        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Usuario no tiene perfil'], 404);
        }

        // Obtener los ranch IDs del usuario
        $ranchIds = Ranch::where('profile_id', $user->profile->id)->pluck('id');

        // Obtener productos de esos ranches
        $query = Product::query()
            ->with(['ranch.address.city.state.country', 'images'])
            ->whereIn('ranch_id', $ranchIds)
            ->orderByDesc('created_at');

        $perPage = (int) $request->input('per_page', 20);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Subir imágenes para un producto
     * POST /api/products/{product}/images
     */
    public function uploadImages(Request $request, Product $product)
    {
        // Ownership: el producto debe pertenecer al perfil del usuario autenticado
        $authProfileId = optional(Auth::user()->profile)->id;
        if (optional($product->ranch)->profile_id !== $authProfileId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Validar que se enviaron imágenes
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:10240', // 10MB max por imagen
        ]);

        $baseUrl = env('APP_ENV') === 'production'
            ? env('APP_URL_PRODUCTION')
            : env('APP_URL_LOCAL');

        $uploadedImages = [];
        $sortOrder = $product->images()->max('sort_order') ?? 0;

        foreach ($request->file('images') as $index => $image) {
            // Guardar la imagen
            $path = $image->store('product_images', 'public');
            $imageUrl = $baseUrl . '/storage/' . $path;

            // Crear el registro ProductImage
            $productImage = $product->images()->create([
                'file_url' => $imageUrl,
                'file_type' => 'image',
                'is_primary' => $index === 0 && $product->images()->count() === 0, // Primera imagen es principal si no hay otras
                'sort_order' => ++$sortOrder,
                'file_size' => $image->getSize(),
                'format' => $image->getClientOriginalExtension(),
            ]);

            $uploadedImages[] = $productImage;
        }

        return response()->json([
            'message' => 'Imágenes subidas exitosamente',
            'images' => $uploadedImages,
            'product' => $product->fresh(['ranch.address.city.state.country', 'images']),
        ], 201);
    }
}


