<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * FavoriteController - Gestión de Productos Favoritos
 * 
 * Maneja las operaciones de favoritos de los usuarios:
 * - Listar favoritos del usuario autenticado
 * - Toggle favorito (agregar/remover)
 * - Verificar si un producto es favorito
 * - Remover de favoritos
 */
class FavoriteController extends Controller
{
    /**
     * GET /api/me/favorites
     * Obtener lista de favoritos del usuario autenticado con paginación
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $profileId = Auth::user()->profile->id;
            $perPage = $request->get('per_page', 20);
            
            Log::info("📋 Obteniendo favoritos del perfil: $profileId");
            
            // Usar el método del modelo con paginación
            $favorites = Favorite::where('profile_id', $profileId)
                ->with([
                    'product.ranch.profile',
                    'product.images' => function ($query) {
                        $query->where('is_primary', true); // ✅ Solo buscar imagen principal
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            Log::info("✅ Favoritos encontrados: {$favorites->count()}");
            
            return response()->json($favorites);
        } catch (\Exception $e) {
            Log::error("❌ Error al obtener favoritos: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al obtener favoritos',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/products/{product}/favorite
     * Toggle favorito (agregar si no existe, remover si existe)
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle($productId)
    {
        try {
            // Verificar que el producto existe
            $product = Product::findOrFail($productId);
            
            $profileId = Auth::user()->profile->id;
            
            Log::info("🔄 Toggle favorito - Perfil: $profileId, Producto: $productId");
            
            // Verificar estado actual
            $wasFavorite = Favorite::isFavorite($profileId, $productId);
            
            // Toggle
            Favorite::toggleFavorite($profileId, $productId);
            
            // Nuevo estado
            $isFavorite = !$wasFavorite;
            
            $message = $isFavorite 
                ? 'Producto agregado a favoritos' 
                : 'Producto removido de favoritos';
            
            Log::info("✅ Toggle favorito exitoso - Estado: " . ($isFavorite ? 'favorito' : 'no favorito'));
            
            return response()->json([
                'success' => true,
                'is_favorite' => $isFavorite,
                'message' => $message,
                'product_id' => $productId,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("⚠️ Producto no encontrado: $productId");
            return response()->json([
                'success' => false,
                'error' => 'Producto no encontrado'
            ], 404);
        } catch (\Exception $e) {
            Log::error("❌ Error al toggle favorito: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al actualizar favorito',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/products/{product}/is-favorite
     * Verificar si un producto es favorito del usuario autenticado
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function check($productId)
    {
        try {
            $profileId = Auth::user()->profile->id;
            
            $isFavorite = Favorite::isFavorite($profileId, $productId);
            
            Log::info("🔍 Check favorito - Perfil: $profileId, Producto: $productId, Resultado: " . ($isFavorite ? 'SÍ' : 'NO'));
            
            return response()->json([
                'is_favorite' => $isFavorite,
                'product_id' => $productId,
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error al verificar favorito: " . $e->getMessage());
            return response()->json([
                'is_favorite' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/products/{product}/favorite
     * Remover producto de favoritos (alternativa al toggle)
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId)
    {
        try {
            $profileId = Auth::user()->profile->id;
            
            Log::info("🗑️ Removiendo favorito - Perfil: $profileId, Producto: $productId");
            
            $removed = Favorite::removeFromFavorites($profileId, $productId);
            
            if ($removed) {
                Log::info("✅ Favorito removido exitosamente");
                return response()->json([
                    'success' => true,
                    'message' => 'Producto removido de favoritos'
                ]);
            } else {
                Log::warning("⚠️ El producto no estaba en favoritos");
                return response()->json([
                    'success' => false,
                    'message' => 'El producto no estaba en favoritos'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error("❌ Error al remover favorito: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error al remover favorito',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/products/{product}/favorites-count
     * Obtener el número de veces que un producto fue marcado como favorito
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function count($productId)
    {
        try {
            $count = Favorite::getProductFavoritesCount($productId);
            
            return response()->json([
                'product_id' => $productId,
                'favorites_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Error al contar favoritos: " . $e->getMessage());
            return response()->json([
                'favorites_count' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

