<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;
use App\Models\Ranch;
use App\Models\Profile;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RanchController extends Controller
{
    /**
     * Store a newly created ranch in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'business_description' => 'nullable|string',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'business_license_url' => 'nullable|string|max:500',
            'contact_hours' => 'nullable|string|max:255',
            'delivery_policy' => 'nullable|string',
            'return_policy' => 'nullable|string',
            'accepts_visits' => 'sometimes|boolean',
            'address_id' => 'nullable|exists:addresses,id', // TEMPORAL: Hacer opcional para debug
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Obtener el usuario autenticado
            $user = Auth::user();
            $profile = $user->profile;

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found'
                ], 404);
            }

            // Crear la hacienda
            $ranch = Ranch::create([
                'profile_id' => $profile->id,
                'name' => $request->name,
                'legal_name' => $request->legal_name,
                'tax_id' => $request->tax_id,
                'business_description' => $request->business_description,
                'certifications' => $request->certifications,
                'business_license_url' => $request->business_license_url,
                'contact_hours' => $request->contact_hours,
                'accepts_visits' => $request->accepts_visits ?? false,
                'address_id' => $request->address_id,
                'delivery_policy' => $request->delivery_policy,
                'return_policy' => $request->return_policy,
                'is_primary' => true, // Primera hacienda es principal
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ranch created successfully',
                'data' => $ranch->load('address', 'profile', 'documents')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating ranch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified ranch.
     */
    public function show(Ranch $ranch)
    {
        return response()->json([
            'success' => true,
            'data' => $ranch->load('address', 'profile', 'documents')
        ]);
    }

    /**
     * Get ALL ranches (public endpoint with pagination).
     * GET /api/ranches
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 20);
        $page = $request->get('page', 1);

        $ranches = Ranch::with([
                'address.city.state.country', // Eager load nested relations
                'profile',
                'documents'
            ])
            ->withCount([
                'products' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json($ranches);
    }

    /**
     * Obtener haciendas del usuario autenticado
     * GET /api/me/ranches
     */
    public function myRanches(Request $request)
    {
        $user = $request->user();
        
        if (!$user || !$user->profile) {
            return response()->json(['message' => 'Usuario no tiene perfil'], 404);
        }

        $ranches = Ranch::where('profile_id', $user->profile->id)
            ->with([
                'address',
                'address.city',
                'address.city.state',
                'address.city.state.country',
                'documents'
            ])
            ->withCount([
                'products' => function ($query) {
                    $query->where('status', 'active');
                }
            ])
            ->orderBy('is_primary', 'desc') // Principal primero
            ->orderBy('created_at', 'desc')
            ->get();

        // Agregar products_count a cada ranch
        $ranches->map(function ($ranch) {
            $ranch->products_count = $ranch->products_count ?? 0;
            return $ranch;
        });

        return response()->json($ranches);
    }

    /**
     * Obtener ranches de un perfil específico (para perfil público)
     */
    public function getByProfile($profileId)
    {
        $ranches = Ranch::where('profile_id', $profileId)
            ->with('address')
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ranches);
    }

    /**
     * Update the specified ranch in storage.
     * PUT /api/ranches/{id}
     */
    public function update(Request $request, Ranch $ranch)
    {
        // Verificar ownership: el ranch debe pertenecer al perfil del usuario autenticado
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile || $ranch->profile_id != $profile->id) { // Usar != en lugar de !== para comparar String vs Int
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You do not own this ranch'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'business_description' => 'nullable|string',
            'certifications' => 'nullable|array',
            'certifications.*' => 'string|max:255',
            'business_license_url' => 'nullable|string|max:500',
            'contact_hours' => 'nullable|string|max:255',
            'accepts_visits' => 'sometimes|boolean',
            'address_id' => 'nullable|exists:addresses,id',
            'is_primary' => 'sometimes|boolean',
            'delivery_policy' => 'nullable|string',
            'return_policy' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si se marca como principal, desmarcar otras
            if ($request->has('is_primary') && $request->is_primary) {
                Ranch::where('profile_id', $profile->id)
                    ->where('id', '!=', $ranch->id)
                    ->update(['is_primary' => false]);
            }

            // Actualizar ranch
            $ranch->update($request->only([
                'name',
                'legal_name',
                'tax_id',
                'business_description',
                'certifications',
                'business_license_url',
                'contact_hours',
                'accepts_visits',
                'address_id',
                'is_primary',
                'delivery_policy',
                'return_policy',
            ]));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ranch updated successfully',
                'data' => $ranch->fresh(['address', 'profile', 'documents'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating ranch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified ranch from storage.
     * DELETE /api/ranches/{id}
     */
    public function destroy(Ranch $ranch)
    {
        // Verificar ownership
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile || $ranch->profile_id !== $profile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You do not own this ranch'
            ], 403);
        }

        // Verificar que no tenga productos activos
        $activeProductsCount = $ranch->products()->where('status', 'active')->count();

        if ($activeProductsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete ranch with active products',
                'active_products_count' => $activeProductsCount
            ], 422);
        }

        // Verificar que no sea la única hacienda (se requiere al menos una)
        $totalRanches = Ranch::where('profile_id', $profile->id)->count();

        if ($totalRanches <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the only ranch. At least one ranch is required'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Si era primary, asignar otra como primary
            if ($ranch->is_primary) {
                $newPrimary = Ranch::where('profile_id', $profile->id)
                    ->where('id', '!=', $ranch->id)
                    ->first();
                
                if ($newPrimary) {
                    $newPrimary->update(['is_primary' => true]);
                }
            }

            // Soft delete
            $ranch->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ranch deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting ranch',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar hacienda a favoritos
     * POST /api/ranches/{id}/favorite
     */
    public function addToFavorites($id)
    {
        try {
            $user = Auth::user();
            $ranch = Ranch::findOrFail($id);

            // Verificar si ya está en favoritos
            $exists = DB::table('favorite_ranches')
                ->where('user_id', $user->id)
                ->where('ranch_id', $ranch->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ranch already in favorites'
                ], 409);
            }

            // Agregar a favoritos
            DB::table('favorite_ranches')->insert([
                'user_id' => $user->id,
                'ranch_id' => $ranch->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ranch added to favorites'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding to favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quitar hacienda de favoritos
     * DELETE /api/ranches/{id}/favorite
     */
    public function removeFromFavorites($id)
    {
        try {
            $user = Auth::user();
            $ranch = Ranch::findOrFail($id);

            $deleted = DB::table('favorite_ranches')
                ->where('user_id', $user->id)
                ->where('ranch_id', $ranch->id)
                ->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ranch not in favorites'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ranch removed from favorites'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing from favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener haciendas favoritas del usuario
     * GET /api/me/favorite-ranches
     */
    public function myFavorites()
    {
        try {
            $user = Auth::user();

            $favoriteRanches = Ranch::with(['address.city.state.country', 'profile'])
                ->whereHas('favoritedByUsers', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $favoriteRanches
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching favorite ranches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar review a una hacienda
     * POST /api/ranches/{id}/reviews
     */
    public function addReview(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $ranch = Ranch::findOrFail($id);

            // Verificar si el usuario ya dejó una review
            $existingReview = \App\Models\RanchReview::where('user_id', $user->id)
                ->where('ranch_id', $ranch->id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already reviewed this ranch'
                ], 409);
            }

            // Crear review
            $review = \App\Models\RanchReview::create([
                'user_id' => $user->id,
                'ranch_id' => $ranch->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            // Actualizar métricas del rancho
            $ranch->updateMetrics();

            return response()->json([
                'success' => true,
                'message' => 'Review added successfully',
                'data' => $review->load('user')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding review',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener reviews de una hacienda
     * GET /api/ranches/{id}/reviews
     */
    public function getReviews($id)
    {
        try {
            $ranch = Ranch::findOrFail($id);

            $reviews = \App\Models\RanchReview::with('user')
                ->where('ranch_id', $ranch->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reviews
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reviews',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos de una hacienda
     * GET /api/ranches/{id}/products
     */
    public function getProducts($id)
    {
        try {
            $ranch = Ranch::findOrFail($id);

            $products = Product::with('images')
                ->where('ranch_id', $ranch->id)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching ranch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Subir documento de la hacienda (hasta 5 documentos PDF)
     * POST /api/ranches/{ranch}/documents
     */
    public function uploadDocument(Request $request, Ranch $ranch)
    {
        // Verificar ownership
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile || $ranch->profile_id !== $profile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You do not own this ranch'
            ], 403);
        }

        // Validar que no exceda 5 documentos
        $existingCount = $ranch->documents()->count();
        if ($existingCount >= 5) {
            return response()->json([
                'success' => false,
                'message' => 'Maximum limit reached. You can upload up to 5 documents per ranch.'
            ], 422);
        }

        // Validar archivo (solo PDF)
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:10240', // 10MB max, solo PDF
            'certification_type' => 'nullable|string|max:255',
        ]);

        try {
            $baseUrl = env('APP_ENV') === 'production'
                ? env('APP_URL_PRODUCTION')
                : env('APP_URL_LOCAL');

            // Guardar el documento
            $file = $request->file('document');
            $path = $file->store('ranch_documents', 'public');
            $documentUrl = $baseUrl . '/storage/' . $path;

            // Crear registro del documento
            $document = $ranch->documents()->create([
                'certification_type' => $request->certification_type,
                'document_url' => $documentUrl,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'order' => $existingCount, // Orden secuencial
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar documento de la hacienda
     * DELETE /api/ranches/{ranch}/documents/{document}
     */
    public function deleteDocument(Ranch $ranch, $documentId)
    {
        // Verificar ownership
        $user = Auth::user();
        $profile = $user->profile;

        if (!$profile || $ranch->profile_id !== $profile->id) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - You do not own this ranch'
            ], 403);
        }

        $document = $ranch->documents()->findOrFail($documentId);

        try {
            // Eliminar archivo físico (si existe)
            $filePath = str_replace(
                (env('APP_ENV') === 'production' ? env('APP_URL_PRODUCTION') : env('APP_URL_LOCAL')) . '/storage/',
                '',
                $document->document_url
            );
            
            if (\Storage::disk('public')->exists($filePath)) {
                \Storage::disk('public')->delete($filePath);
            }

            // Soft delete el registro
            $document->delete();

            // Reordenar documentos restantes
            $documents = $ranch->documents()->orderBy('order')->get();
            foreach ($documents as $index => $doc) {
                $doc->update(['order' => $index]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting document',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
