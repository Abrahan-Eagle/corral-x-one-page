<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Advertisement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvertisementController extends BaseController
{
    /**
     * Listar todos los anuncios (solo admin)
     */
    public function index(Request $request)
    {
        // Verificar que el usuario es admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Solo administradores pueden ver todos los anuncios'
            ], 403);
        }

        $query = Advertisement::query()
            ->with(['product', 'creator']);

        // Filtros opcionales
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Ordenamiento
        $query->orderBy('priority', 'desc')
              ->orderBy('created_at', 'desc');

        $perPage = (int) $request->input('per_page', 15);
        $advertisements = $query->paginate($perPage);

        return response()->json($advertisements);
    }

    /**
     * Obtener anuncios activos (público - para marketplace)
     */
    public function active(Request $request)
    {
        $query = Advertisement::query()
            ->active() // Scope que filtra por fechas y estado activo
            ->with(['product.images']); // Cargar producto con imágenes si es sponsored_product

        // Filtro por tipo opcional
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        // Ordenar por prioridad (descendente)
        $advertisements = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Para productos patrocinados, asegurar que los datos estén actualizados desde el producto
        // Con la nueva lógica Instagram-like, los datos siempre se obtienen del producto
        foreach ($advertisements as $advertisement) {
            if ($advertisement->type === 'sponsored_product' && $advertisement->product) {
                $product = $advertisement->product;
                // Sincronizar datos del producto en el anuncio (para compatibilidad)
                // Los datos reales siempre vienen del producto en la relación
                $advertisement->title = $product->title;
                $advertisement->description = $product->description;
                $primaryImage = $product->getPrimaryImage();
                if ($primaryImage) {
                    $advertisement->image_url = $primaryImage->file_url;
                } else {
                    // Si no hay imagen principal, intentar la primera
                    $firstImage = $product->images()->first();
                    if ($firstImage) {
                        $advertisement->image_url = $firstImage->file_url;
                    }
                }
                // Asegurar que target_url apunte al producto
                if (empty($advertisement->target_url)) {
                    $advertisement->target_url = '/api/products/' . $product->id;
                }
            }
            $advertisement->incrementImpressions();
        }

        return response()->json([
            'data' => $advertisements,
            'count' => $advertisements->count()
        ]);
    }

    /**
     * Crear un nuevo anuncio (solo admin)
     */
    public function store(Request $request)
    {
        // Verificar que el usuario es admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Solo administradores pueden crear anuncios'
            ], 403);
        }

        // Validación básica (diferente según el tipo)
        $rules = [
            'type' => 'required|in:sponsored_product,external_ad',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
        ];

        // Validación específica según el tipo
        if ($request->type === 'sponsored_product') {
            // Para productos patrocinados: title, description, image_url son OPCIONALES
            // Si no se proporcionan, se obtienen automáticamente del producto
            $rules['product_id'] = 'required|exists:products,id';
            $rules['title'] = 'nullable|string|max:200';
            $rules['description'] = 'nullable|string|max:1000';
            $rules['image_url'] = 'nullable|url|max:500';
            $rules['target_url'] = 'nullable|url|max:500';
        } else {
            // Para publicidad externa: title, description, image_url son OBLIGATORIOS
            $rules['title'] = 'required|string|max:200';
            $rules['description'] = 'nullable|string|max:1000';
            $rules['image_url'] = 'required|url|max:500';
            $rules['target_url'] = 'nullable|url|max:500';
            $rules['advertiser_name'] = 'required|string|max:200';
        }

        $validated = $request->validate($rules);

        // Validación específica para sponsored_product
        if ($request->type === 'sponsored_product') {
            $product = Product::find($request->product_id);
            if (!$product) {
                return response()->json([
                    'error' => 'Producto no encontrado',
                    'message' => 'El producto especificado no existe'
                ], 404);
            }
            if ($product->status !== 'active') {
                return response()->json([
                    'error' => 'Producto no activo',
                    'message' => 'El producto debe estar activo para ser patrocinado'
                ], 422);
            }

            // Obtener datos del producto automáticamente si no se proporcionaron
            if (empty($validated['title'])) {
                $validated['title'] = $product->title;
            }
            if (empty($validated['description'])) {
                $validated['description'] = $product->description;
            }
            if (empty($validated['image_url'])) {
                // Obtener imagen principal del producto
                $primaryImage = $product->getPrimaryImage();
                if ($primaryImage) {
                    $validated['image_url'] = $primaryImage->file_url;
                } else {
                    // Si no hay imagen principal, intentar obtener la primera imagen
                    $firstImage = $product->images()->first();
                    if ($firstImage) {
                        $validated['image_url'] = $firstImage->file_url;
                    } else {
                        return response()->json([
                            'error' => 'Producto sin imagen',
                            'message' => 'El producto debe tener al menos una imagen para ser patrocinado'
                        ], 422);
                    }
                }
            }
            if (empty($validated['target_url'])) {
                // Generar URL del producto (el frontend puede construir la ruta)
                // Usar URL relativa que el frontend pueda manejar
                $validated['target_url'] = '/api/products/' . $product->id;
            }
        }

        // Agregar created_by
        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 0;

        $advertisement = Advertisement::create($validated);
        $advertisement->load(['product', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Anuncio creado exitosamente',
            'data' => $advertisement
        ], 201);
    }

    /**
     * Ver detalle de un anuncio (solo admin)
     */
    public function show(Advertisement $advertisement)
    {
        // Verificar que el usuario es admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Solo administradores pueden ver detalles de anuncios'
            ], 403);
        }

        $advertisement->load(['product', 'creator']);

        return response()->json($advertisement);
    }

    /**
     * Actualizar un anuncio (solo admin)
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        // Verificar que el usuario es admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Solo administradores pueden actualizar anuncios'
            ], 403);
        }

        // Validación básica (diferente según el tipo)
        $rules = [
            'type' => 'sometimes|required|in:sponsored_product,external_ad',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:100',
        ];

        // Determinar el tipo (puede estar cambiando o mantener el actual)
        $adType = $request->input('type', $advertisement->type);

        // Validación específica según el tipo
        if ($adType === 'sponsored_product') {
            // Para productos patrocinados: title, description, image_url son OPCIONALES
            $rules['product_id'] = 'sometimes|required|exists:products,id';
            $rules['title'] = 'nullable|string|max:200';
            $rules['description'] = 'nullable|string|max:1000';
            $rules['image_url'] = 'nullable|url|max:500';
            $rules['target_url'] = 'nullable|url|max:500';
        } else {
            // Para publicidad externa: title, description, image_url son OBLIGATORIOS
            $rules['title'] = 'sometimes|required|string|max:200';
            $rules['description'] = 'nullable|string|max:1000';
            $rules['image_url'] = 'sometimes|required|url|max:500';
            $rules['target_url'] = 'nullable|url|max:500';
            $rules['advertiser_name'] = 'sometimes|required|string|max:200';
        }

        $validated = $request->validate($rules);

        // Validación específica para sponsored_product
        if ($adType === 'sponsored_product') {
            $productId = $validated['product_id'] ?? $advertisement->product_id;
            $product = Product::find($productId);
            if (!$product) {
                return response()->json([
                    'error' => 'Producto no encontrado',
                    'message' => 'El producto especificado no existe'
                ], 404);
            }
            if ($product->status !== 'active') {
                return response()->json([
                    'error' => 'Producto no activo',
                    'message' => 'El producto debe estar activo para ser patrocinado'
                ], 422);
            }

            // Si se está actualizando y no se proporcionaron datos, usar datos del producto
            // O si se cambió el product_id, actualizar datos del nuevo producto
            if (isset($validated['product_id']) && $validated['product_id'] != $advertisement->product_id) {
                // Producto cambió, actualizar datos automáticamente
                $validated['title'] = $product->title;
                $validated['description'] = $product->description;
                $primaryImage = $product->getPrimaryImage();
                if ($primaryImage) {
                    $validated['image_url'] = $primaryImage->file_url;
                } else {
                    $firstImage = $product->images()->first();
                    if ($firstImage) {
                        $validated['image_url'] = $firstImage->file_url;
                    }
                }
                $validated['target_url'] = '/products/' . $product->id;
            } elseif (empty($validated['title']) || empty($validated['image_url'])) {
                // Si no se proporcionaron datos y son necesarios, obtenerlos del producto
                $currentProduct = $product;
                if (empty($validated['title'])) {
                    $validated['title'] = $currentProduct->title;
                }
                if (empty($validated['description'])) {
                    $validated['description'] = $currentProduct->description;
                }
                if (empty($validated['image_url'])) {
                    $primaryImage = $currentProduct->getPrimaryImage();
                    if ($primaryImage) {
                        $validated['image_url'] = $primaryImage->file_url;
                    } else {
                        $firstImage = $currentProduct->images()->first();
                        if ($firstImage) {
                            $validated['image_url'] = $firstImage->file_url;
                        }
                    }
                }
                if (empty($validated['target_url'])) {
                    $validated['target_url'] = '/api/products/' . $currentProduct->id;
                }
            }
        }

        $advertisement->update($validated);
        $advertisement->load(['product', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Anuncio actualizado exitosamente',
            'data' => $advertisement
        ]);
    }

    /**
     * Eliminar un anuncio (solo admin)
     */
    public function destroy(Advertisement $advertisement)
    {
        // Verificar que el usuario es admin
        if (!Auth::user()->isAdmin()) {
            return response()->json([
                'error' => 'No autorizado',
                'message' => 'Solo administradores pueden eliminar anuncios'
            ], 403);
        }

        $advertisement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Anuncio eliminado exitosamente'
        ]);
    }

    /**
     * Registrar click en un anuncio (público)
     */
    public function click(Advertisement $advertisement)
    {
        // Verificar que el anuncio está activo
        if (!$advertisement->isActive()) {
            return response()->json([
                'error' => 'Anuncio no disponible',
                'message' => 'Este anuncio no está activo'
            ], 404);
        }

        $advertisement->incrementClicks();

        return response()->json([
            'success' => true,
            'message' => 'Click registrado',
            'clicks' => $advertisement->clicks
        ]);
    }
}
