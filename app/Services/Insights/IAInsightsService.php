<?php

namespace App\Services\Insights;

use App\Models\Advertisement;
use App\Models\Conversation;
use App\Models\Favorite;
use App\Models\IAInsightUserRecommendation;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IAInsightsService
{
    public function generateDashboard(User $user, string $timeRange = '7d', ?string $roleOverride = null): array
    {
        $range = $this->resolveRange($timeRange);
        $profile = Profile::with('ranches.products')
            ->where('user_id', $user->id)
            ->first();

        $audience = $this->resolveAudience($user, $profile, $roleOverride);

        if ($audience === 'admin') {
            return $this->buildAdminPayload($user, $range);
        }

        if ($audience === 'premium') {
            return $this->buildPremiumPayload($user, $profile, $range);
        }

        return $this->buildFreePayload($user, $profile, $range);
    }

    public function updateRecommendationStatus(User $user, string $key, bool $isCompleted): array
    {
        $record = IAInsightUserRecommendation::updateOrCreate(
            [
                'user_id' => $user->id,
                'recommendation_key' => $key,
            ],
            [
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
            ]
        );

        return [
            'recommendation_key' => $record->recommendation_key,
            'is_completed' => $record->is_completed,
            'completed_at' => $record->completed_at?->toIso8601String(),
        ];
    }

    /** @return array{start:Carbon,end:Carbon,label:string} */
    private function resolveRange(string $timeRange): array
    {
        $end = Carbon::now();

        switch ($timeRange) {
            case '24h':
                $start = $end->copy()->subDay();
                $label = 'Ultimas 24 horas';
                break;
            case '30d':
                $start = $end->copy()->subDays(30);
                $label = 'Ultimos 30 dias';
                break;
            case '90d':
                $start = $end->copy()->subDays(90);
                $label = 'Ultimos 90 dias';
                break;
            default:
                $start = $end->copy()->subDays(7);
                $label = 'Ultimos 7 dias';
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
        ];
    }

    private function resolveAudience(User $user, ?Profile $profile, ?string $override): string
    {
        if ($override) {
            $normalized = strtolower($override);
            if (in_array($normalized, ['admin', 'premium', 'free'], true)) {
                return $normalized;
            }
        }

        if (strtolower((string) $user->role) === 'admin') {
            return 'admin';
        }

        if (
            strtolower((string) $user->role) === 'premium'
            || ($profile?->is_premium_seller)
        ) {
            return 'premium';
        }

        return 'free';
    }

    /** @param array{start:Carbon,end:Carbon,label:string} $range */
    private function buildFreePayload(User $user, ?Profile $profile, array $range): array
    {
        $ranchIds = $profile?->ranches->pluck('id') ?? collect();
        $productQuery = Product::query()
            ->when($ranchIds->isNotEmpty(), fn (Builder $q) => $q->whereIn('ranch_id', $ranchIds));

        $products = $productQuery->get(['id', 'views', 'status', 'price', 'is_featured', 'created_at']);
        $productIds = $products->pluck('id');

        $views = (int) $products->sum('views');
        $activeProducts = $products->where('status', 'active')->count();
        $featuredProducts = $products->where('is_featured', true)->count();
        $averagePrice = (float) ($products->avg('price') ?? 0);

        $favoritesCount = $productIds->isNotEmpty()
            ? Favorite::whereIn('product_id', $productIds)
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->count()
            : 0;

        $conversationsQuery = $profile
            ? Conversation::where(function (Builder $query) use ($profile) {
                $query->where('profile_id_1', $profile->id)
                    ->orWhere('profile_id_2', $profile->id);
            })
            : Conversation::query()->whereRaw('0 = 1');

        $conversationsCount = $conversationsQuery
            ->whereBetween('created_at', [$range['start'], $range['end']])
            ->count();

        $metrics = [
            $this->metric(
                'free_views',
                'Vistas de publicaciones',
                $views,
                $views > 0 ? 'up' : 'flat',
                'marketplace',
                $favoritesCount > 0
                    ? 'Los anuncios generan interés constante.'
                    : 'Actualiza tus fotos para impulsar las vistas.'
            ),
            $this->metric(
                'free_favorites',
                'Favoritos',
                $favoritesCount,
                $favoritesCount > 0 ? 'up' : 'flat',
                'marketplace',
                $favoritesCount > 0
                    ? 'Tus publicaciones están generando leads valiosos.'
                    : 'Aplica descripciones claras para ganar favoritos.'
            ),
            $this->metric(
                'free_chats',
                'Conversaciones iniciadas',
                $conversationsCount,
                $conversationsCount > 0 ? 'up' : 'down',
                'chat',
                $conversationsCount > 0
                    ? 'Mantén respuestas rápidas para cerrar ventas.'
                    : 'Invita a contactar agregando CTA en la descripción.'
            ),
        ];

        $recommendations = $this->decorateRecommendations($user, [
            $this->recommendation(
                'free-refresh-photos',
                'Actualiza fotos clave',
                'Renueva las imágenes de tus lotes más antiguos para mejorar el ratio vistas → favoritos.',
                'medium',
                'marketplace',
                '+15 favoritos potenciales'
            ),
            $this->recommendation(
                'free-respond-chat',
                'Responde chats pendientes',
                'Acelera tus respuestas: contestar en menos de 1 hora duplica las probabilidades de cierre.',
                'high',
                'chat',
                'Reduce tiempos de venta'
            ),
            $this->recommendation(
                'free-highlight-product',
                'Destaca tu publicación estrella',
                'Promociona el lote con mejor desempeño para mantener el flujo de mensajes.',
                'low',
                'ads',
                'Mayor visibilidad'
            ),
        ]);

        $extras = array_filter([
            'user_id' => $user->id,
            'profile_id' => $profile?->id,
            'active_products' => $activeProducts,
            'featured_products' => $featuredProducts,
            'average_price' => round($averagePrice, 2),
        ], fn ($value) => $value !== null);

        $payload = $this->payload(
            role: 'free',
            label: $range['label'],
            metrics: $metrics,
            recommendations: $recommendations,
            summaryHeadline: sprintf(
                'Tus publicaciones sumaron %s vistas y %s favoritos en %s',
                $this->formatNumber($views),
                $this->formatNumber($favoritesCount),
                strtolower($range['label'])
            ),
            summaryDescription: $conversationsCount > 0
                ? 'Sigue conversando con tus clientes; las respuestas rápidas sostienen el interés.'
                : 'Activa recordatorios para responder chats y captar mejores prospectos.',
            projections: $activeProducts > 0
                ? [
                    $this->metric(
                        'free_projection_leads',
                        'Leads estimados (próximos 7 días)',
                        max(1, (int) round($favoritesCount * 0.6 + $conversationsCount * 0.8)),
                        'up',
                        'forecast',
                        'Basado en el comportamiento reciente.'
                    ),
                ]
                : [],
            extras: [
                ...$extras,
            ]
        );

        return $this->augmentWithAi($payload);
    }

    /** @param array{start:Carbon,end:Carbon,label:string} $range */
    private function buildPremiumPayload(User $user, ?Profile $profile, array $range): array
    {
        $ranchIds = $profile?->ranches->pluck('id') ?? collect();
        $productQuery = Product::query()
            ->when($ranchIds->isNotEmpty(), fn (Builder $q) => $q->whereIn('ranch_id', $ranchIds));

        $products = $productQuery->get(['id', 'views', 'status', 'price', 'is_featured', 'created_at']);
        $productIds = $products->pluck('id');

        $views = (int) $products->sum('views');
        $featured = $products->where('is_featured', true)->count();
        $active = $products->where('status', 'active')->count();
        $avgPrice = (float) ($products->avg('price') ?? 0);

        $favoritesCount = $productIds->isNotEmpty()
            ? Favorite::whereIn('product_id', $productIds)
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->count()
            : 0;

        $conversationsCount = $profile
            ? Conversation::where(function (Builder $query) use ($profile) {
                $query->where('profile_id_1', $profile->id)
                    ->orWhere('profile_id_2', $profile->id);
            })
                ->whereBetween('created_at', [$range['start'], $range['end']])
                ->count()
            : 0;

        $conversionRate = $views > 0 ? ($conversationsCount / max(1, $views)) * 100 : 0;

        $metrics = [
            $this->metric(
                'premium_views',
                'Vistas totales',
                $views,
                $views > 0 ? 'up' : 'flat',
                'marketplace',
                'Revisa qué lotes reciben mayor atención y replica la fórmula.'
            ),
            $this->metric(
                'premium_conversion_rate',
                'Conversión a chat',
                round($conversionRate, 1),
                $conversionRate >= 5 ? 'up' : 'down',
                'chat',
                'Tus conversaciones activas reflejan el interés directo del comprador.',
                unit: '%'
            ),
            $this->metric(
                'premium_featured',
                'Publicaciones destacadas',
                $featured,
                $featured > 0 ? 'up' : 'flat',
                'ads',
                'Mantén al menos 2 lotes destacados para liderar en visibilidad.'
            ),
        ];

        $recommendations = $this->decorateRecommendations($user, [
            $this->recommendation(
                'premium-boost-top-product',
                'Potencia tu lote estrella',
                'Activa un anuncio patrocinado para el lote con más favoritos: consolida su posición en la portada.',
                'high',
                'ads',
                '+28% contactos estimados'
            ),
            $this->recommendation(
                'premium-adjust-pricing',
                'Ajusta precios estratégicos',
                'Compara tus precios con la media regional y ajusta ±8% para mejorar tu conversión.',
                'medium',
                'pricing',
                'Mayor cierre de acuerdos'
            ),
            $this->recommendation(
                'premium-automate-chat',
                'Automatiza respuestas fuera de horario',
                'Configura mensajes de bienvenida para mantener el engagement nocturno.',
                'low',
                'chat',
                'Conserva tu badge de atención'
            ),
        ]);

        $projections = [
            $this->metric(
                'premium_forecast_sales',
                'Proyección de ventas (30 días)',
                max(1, (int) round($conversationsCount * 0.35)),
                $conversationsCount > 0 ? 'up' : 'flat',
                'forecast',
                'Estimación basada en tu ratio de cierre actual.'
            ),
        ];

        $extras = array_filter([
            'user_id' => $user->id,
            'profile_id' => $profile?->id,
            'active_products' => $active,
            'average_price' => round($avgPrice, 2),
            'time_range_start' => $range['start']->toIso8601String(),
        ], fn ($value) => $value !== null);

        $payload = $this->payload(
            role: 'premium',
            label: $range['label'],
            metrics: $metrics,
            recommendations: $recommendations,
            summaryHeadline: sprintf(
                'Tu hacienda se posiciona con %s vistas y %s conversaciones',
                $this->formatNumber($views),
                $this->formatNumber($conversationsCount)
            ),
            summaryDescription: 'Sigue optimizando horarios y campañas patrocinadas para aprovechar tu nivel premium.',
            projections: $projections,
            extras: [
                ...$extras,
            ]
        );

        return $this->augmentWithAi($payload);
    }

    /** @param array{start:Carbon,end:Carbon,label:string} $range */
    private function buildAdminPayload(User $user, array $range): array
    {
        $totalUsers = User::count();
        $activeProducts = Product::where('status', 'active')->count();
        $totalViews = (int) Product::sum('views');
        $reportsLastWindow = Report::whereBetween('created_at', [$range['start'], $range['end']])->count();
        $advertisements = Advertisement::count();
        $conversations = Conversation::whereBetween('created_at', [$range['start'], $range['end']])->count();

        $metrics = [
            $this->metric(
                'admin_active_users',
                'Usuarios activos',
                $totalUsers,
                'up',
                'global',
                'Los registros muestran adopción estable.'
            ),
            $this->metric(
                'admin_market_health',
                'Productos activos',
                $activeProducts,
                $activeProducts > 0 ? 'up' : 'flat',
                'marketplace',
                'Monitorea la calidad de los listados destacados.'
            ),
            $this->metric(
                'admin_reports',
                'Reportes recibidos',
                $reportsLastWindow,
                $reportsLastWindow > 5 ? 'down' : 'flat',
                'moderation',
                'Gestiona prioridades con el equipo de soporte.'
            ),
        ];

        $recommendations = $this->decorateRecommendations($user, [
            $this->recommendation(
                'admin-review-pricing',
                'Revisar anomalías de precios',
                'Detectamos variaciones fuertes en la categoría engorde. Ejecuta la auditoría semanal.',
                'high',
                'moderation',
                'Reduce fraude y mantiene confianza'
            ),
            $this->recommendation(
                'admin-campaign-education',
                'Campaña sanitaria informativa',
                'Refuerza contenidos sobre Fiebre Aftosa para compradores y vendedores.',
                'medium',
                'education',
                'Mejora compliance sanitario'
            ),
            $this->recommendation(
                'admin-highlight-premium',
                'Destacar casos de éxito premium',
                'Publica métricas de conversión premium para incentivar upgrades.',
                'low',
                'growth',
                'Aumenta upsells'
            ),
        ]);

        $projections = [
            $this->metric(
                'admin_forecast_sellers',
                'Nuevos vendedores proyectados (30 días)',
                max(1, (int) round($totalUsers * 0.05)),
                'up',
                'growth',
                'Estimación basada en la tendencia de registro.'
            ),
        ];

        $extras = array_filter([
            'user_id' => $user->id,
            'advertisements_total' => $advertisements,
            'range_start' => $range['start']->toIso8601String(),
        ], fn ($value) => $value !== null);

        $payload = $this->payload(
            role: 'admin',
            label: $range['label'],
            metrics: $metrics,
            recommendations: $recommendations,
            summaryHeadline: sprintf(
                'El marketplace registra %s vistas y %s conversaciones en %s',
                $this->formatNumber($totalViews),
                $this->formatNumber($conversations),
                strtolower($range['label'])
            ),
            summaryDescription: 'Coordina a los equipos de moderación y educación para sostener el crecimiento.',
            projections: $projections,
            extras: [
                ...$extras,
            ]
        );

        return $this->augmentWithAi($payload);
    }

    private function metric(
        string $id,
        string $title,
        float|int $value,
        string $trend,
        string $segment,
        ?string $description = null,
        ?string $unit = null
    ): array {
        return array_filter([
            'id' => $id,
            'title' => $title,
            'display_value' => $unit === '%' ? $this->formatNumber($value, 1) . '%' : $this->formatNumber($value),
            'trend_direction' => $trend,
            'segment' => $segment,
            'description' => $description,
            'unit' => $unit,
        ], fn ($item) => $item !== null);
    }

    private function recommendation(
        string $id,
        string $title,
        string $description,
        string $priority,
        string $segment,
        ?string $impact = null
    ): array {
        return array_filter([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'segment' => $segment,
            'impact_text' => $impact,
            'generated_at' => now()->toIso8601String(),
        ], fn ($item) => $item !== null);
    }

    private function decorateRecommendations(User $user, array $recommendations): array
    {
        $keys = Arr::pluck($recommendations, 'id');
        $states = $this->fetchRecommendationStates($user->id, $keys);

        return array_map(function (array $recommendation) use ($states) {
            $state = $states->get($recommendation['id']);

            return array_merge($recommendation, [
                'is_completed' => $state?->is_completed ?? false,
                'completed_at' => $state?->completed_at?->toIso8601String(),
            ]);
        }, $recommendations);
    }

    private function fetchRecommendationStates(int $userId, array $keys): Collection
    {
        return IAInsightUserRecommendation::query()
            ->where('user_id', $userId)
            ->whereIn('recommendation_key', $keys)
            ->get()
            ->keyBy('recommendation_key');
    }

    private function formatNumber(float|int $value, int $decimals = 0): string
    {
        if ($value >= 1000) {
            return number_format($value / 1000, 1 + $decimals) . 'K';
        }

        return number_format($value, $decimals);
    }

    private function payload(
        string $role,
        string $label,
        array $metrics,
        array $recommendations,
        string $summaryHeadline,
        string $summaryDescription,
        array $projections = [],
        array $extras = []
    ): array {
        return array_merge([
            'role' => $role,
            'time_range_label' => $label,
            'generated_at' => now()->toIso8601String(),
            'summary_headline' => $summaryHeadline,
            'summary_description' => $summaryDescription,
            'metrics' => $metrics,
            'recommendations' => $recommendations,
            'projections' => $projections,
            'raw' => [
                'extras' => $extras,
            ],
            'is_mock' => false,
        ], $extras);
    }

    private function augmentWithAi(array $payload): array
    {
        $apiKey = trim((string) config('services.google_gen_ai.api_key', ''));

        if ($apiKey === '' || $apiKey === 'replace-me') {
            return $payload;
        }

        try {
            $aiData = $this->callGemini($payload);
        } catch (\Throwable $exception) {
            Log::warning('IAInsightsService: Gemini request failed', [
                'message' => $exception->getMessage(),
            ]);

            $payload['raw']['ai_error'] = $exception->getMessage();

            return $payload;
        }

        if ($aiData === null) {
            return $payload;
        }

        if (!empty($aiData['headline'])) {
            $payload['summary_headline'] = $aiData['headline'];
        }

        if (!empty($aiData['summary'])) {
            $payload['summary_description'] = $aiData['summary'];
        }

        if (!empty($aiData['premium_tip']) && $payload['role'] === 'premium') {
            $payload['raw']['ai_premium_tip'] = $aiData['premium_tip'];
        }

        if (!empty($aiData['admin_alert']) && $payload['role'] === 'admin') {
            $payload['raw']['ai_admin_alert'] = $aiData['admin_alert'];
        }

        $role = $payload['role'] ?? 'free';
        $userId = $payload['user_id'] ?? null;
        $existingIds = Arr::pluck($payload['recommendations'], 'id');

        $aiRecommendations = [];
        if (!empty($aiData['extra_recommendations']) && is_array($aiData['extra_recommendations'])) {
            foreach ($aiData['extra_recommendations'] as $index => $recommendationText) {
                $aiRecommendations[] = [
                    'key' => sprintf('ai-%s-%d', $role, $index + 1),
                    'description' => $recommendationText,
                ];
            }
        }

        $stateMap = collect();
        if ($userId) {
            $keysToFetch = array_merge(
                $existingIds,
                array_map(fn (array $item) => $item['key'], $aiRecommendations)
            );

            if (!empty($keysToFetch)) {
                $stateMap = $this->fetchRecommendationStates((int) $userId, $keysToFetch);
            }
        }

        foreach ($aiRecommendations as $item) {
            if (in_array($item['key'], $existingIds, true)) {
                continue;
            }

            $state = $stateMap->get($item['key']);

            $payload['recommendations'][] = [
                'id' => $item['key'],
                'title' => 'Sugerencia IA',
                'description' => $item['description'],
                'priority' => 'medium',
                'segment' => 'ai',
                'impact_text' => null,
                'generated_at' => now()->toIso8601String(),
                'is_completed' => $state?->is_completed ?? false,
                'completed_at' => $state?->completed_at?->toIso8601String(),
                'is_ai_generated' => true,
            ];

            $existingIds[] = $item['key'];
        }

        $payload['raw']['ai'] = $aiData;

        return $payload;
    }

    private function callGemini(array $payload): ?array
    {
        $apiKey = config('services.google_gen_ai.api_key');
        $baseUrl = rtrim(config('services.google_gen_ai.base_url', 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $model = ltrim(config('services.google_gen_ai.model', 'models/gemini-2.0-flash'), '/');

        if (empty($apiKey)) {
            return null;
        }

        $endpoint = sprintf('%s/%s:generateContent', $baseUrl, $model);

        $prompt = $this->buildPromptForGemini($payload);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $apiKey,
        ])->post($endpoint, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_NONE',
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'topK' => 32,
                'topP' => 0.8,
                'maxOutputTokens' => 512,
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(sprintf(
                'Gemini request failed with status %s: %s',
                $response->status(),
                (string) $response->body()
            ));
        }

        $data = $response->json();
        $rawText = data_get($data, 'candidates.0.content.parts.0.text');

        if (!$rawText) {
            return null;
        }

        $decoded = $this->decodeGeminiJson($rawText);

        return is_array($decoded) ? $decoded : null;
    }

    private function buildPromptForGemini(array $payload): string
    {
        $summary = [
            'role' => $payload['role'] ?? 'desconocido',
            'time_range_label' => $payload['time_range_label'] ?? '',
            'metrics' => $payload['metrics'] ?? [],
            'recommendations' => $payload['recommendations'] ?? [],
            'projections' => $payload['projections'] ?? [],
            'raw' => $payload['raw'] ?? [],
        ];

        $json = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<PROMPT
Eres un analista experto en agronegocios bovinos. 
Resume la situación del usuario tomando el JSON que se provee, genera un titular atractivo, 
un párrafo de resumen (máximo 60 palabras) y una lista de recomendaciones adicionales.

El resultado debe ser exclusivamente JSON válido, sin texto extra ni bloques markdown, con esta estructura:
{
  "headline": "...",
  "summary": "...",
  "extra_recommendations": ["...", "..."],
  "premium_tip": "...", // opcional, solo si el rol es premium
  "admin_alert": "..."   // opcional, solo si el rol es admin
}

Datos:
{$json}
PROMPT;
    }

    private function decodeGeminiJson(string $rawText): ?array
    {
        $clean = trim($rawText);

        if (str_starts_with($clean, '```')) {
            $clean = preg_replace('/^```[a-zA-Z]*\n?/', '', $clean);
            $clean = preg_replace('/```$/', '', $clean);
        }

        $clean = trim($clean);
        $decoded = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('IAInsightsService: Gemini JSON decode error', [
                'error' => json_last_error_msg(),
                'raw' => $clean,
            ]);

            return null;
        }

        return $decoded;
    }
}

