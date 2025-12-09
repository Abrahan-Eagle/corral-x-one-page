<?php

namespace Tests\Feature;

use App\Models\IAInsightUserRecommendation;
use App\Models\Profile;
use App\Models\User;
use App\Services\Insights\IAInsightsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IAInsightsAiRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_recommendations_keep_completed_state(): void
    {
        config()->set('services.google_gen_ai.api_key', 'testing-key');
        config()->set('services.google_gen_ai.base_url', 'https://fake-gemini.test/v1beta');
        config()->set('services.google_gen_ai.model', 'models/fake');

        Http::fake([
            'https://fake-gemini.test/v1beta/models/fake:generateContent' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'headline' => 'Titular generado',
                                        'summary' => 'Resumen generado por IA.',
                                        'extra_recommendations' => [
                                            'Primera recomendación generada por IA',
                                        ],
                                    ], JSON_UNESCAPED_UNICODE),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create(['role' => 'users']);
        Profile::factory()->for($user)->create(['is_premium_seller' => true]);

        IAInsightUserRecommendation::create([
            'user_id' => $user->id,
            'recommendation_key' => 'ai-premium-1',
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        /** @var IAInsightsService $service */
        $service = app(IAInsightsService::class);

        $payload = $service->generateDashboard($user, '7d');

        $aiRecommendation = collect($payload['recommendations'])
            ->firstWhere('id', 'ai-premium-1');

        $this->assertNotNull($aiRecommendation, 'Debe existir la recomendación IA generada.');
        $this->assertTrue($aiRecommendation['is_completed'], 'La recomendación IA debe mantenerse como completada.');
        $this->assertNotEmpty($aiRecommendation['completed_at']);
    }
}


