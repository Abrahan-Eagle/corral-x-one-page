<?php

namespace Tests\Unit;

use App\Models\Profile;
use App\Models\Ranch;
use App\Models\User;
use App\Services\KycEvaluationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KycEvaluationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private KycEvaluationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KycEvaluationService();
    }

    public function test_evaluate_sets_verified_when_gemini_approves(): void
    {
        // Mock Gemini response
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'decision' => 'verified',
                                        'reasons' => ['Todos los datos son consistentes'],
                                        'confidence' => 'high',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        // Configurar API key para activar Gemini
        config(['services.google_gen_ai.api_key' => 'test-key']);

        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'ci_number' => 'V-12345678',
            'kyc_status' => 'pending',
            'kyc_doc_front_path' => 'test/front.jpg',
            'kyc_rif_path' => 'test/rif.jpg',
            'kyc_selfie_path' => 'test/selfie.jpg',
            'kyc_selfie_with_doc_path' => 'test/selfie_doc.jpg',
        ]);

        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => true,
            'tax_id' => 'J-12345678-9',
        ]);

        $this->service->evaluate($profile);

        $profile->refresh();
        $this->assertSame('verified', $profile->kyc_status);
        $this->assertNotNull($profile->kyc_verified_at);
        $this->assertNull($profile->kyc_rejection_reason);
    }

    public function test_evaluate_sets_rejected_when_gemini_rejects(): void
    {
        // Mock Gemini response
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'decision' => 'rejected',
                                        'reasons' => ['Inconsistencia en nombres', 'CI no coincide con perfil'],
                                        'confidence' => 'high',
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        config(['services.google_gen_ai.api_key' => 'test-key']);

        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'ci_number' => 'V-12345678',
            'kyc_status' => 'pending',
            'kyc_doc_front_path' => 'test/front.jpg',
            'kyc_rif_path' => 'test/rif.jpg',
            'kyc_selfie_path' => 'test/selfie.jpg',
            'kyc_selfie_with_doc_path' => 'test/selfie_doc.jpg',
        ]);

        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => true,
            'tax_id' => 'J-12345678-9',
        ]);

        $this->service->evaluate($profile);

        $profile->refresh();
        $this->assertSame('rejected', $profile->kyc_status);
        $this->assertNotNull($profile->kyc_rejection_reason);
        $this->assertStringContainsString('Inconsistencia', $profile->kyc_rejection_reason);
    }

    public function test_evaluate_falls_back_to_local_when_gemini_unavailable(): void
    {
        // Sin API key, Gemini no se llama
        config(['services.google_gen_ai.api_key' => null]);

        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'ci_number' => 'V-12345678',
            'kyc_status' => 'pending',
            'kyc_doc_front_path' => 'test/front.jpg',
            'kyc_rif_path' => 'test/rif.jpg',
            'kyc_selfie_path' => 'test/selfie.jpg',
            'kyc_selfie_with_doc_path' => 'test/selfie_doc.jpg',
        ]);

        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => true,
            'tax_id' => 'J-12345678-9',
        ]);

        $this->service->evaluate($profile);

        $profile->refresh();
        // Debe usar validación local y verificar
        $this->assertSame('verified', $profile->kyc_status);
    }

    public function test_evaluate_sets_pending_when_local_validation_fails(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'ci_number' => 'INVALID-CI', // CI inválida
            'kyc_status' => 'no_verified',
            'kyc_doc_front_path' => 'test/front.jpg',
            'kyc_rif_path' => 'test/rif.jpg',
            'kyc_selfie_path' => 'test/selfie.jpg',
            'kyc_selfie_with_doc_path' => 'test/selfie_doc.jpg',
        ]);

        $this->service->evaluate($profile);

        $profile->refresh();
        $this->assertSame('pending', $profile->kyc_status);
        $this->assertNotNull($profile->kyc_rejection_reason);
    }

    public function test_evaluate_does_nothing_when_already_verified(): void
    {
        $user = User::factory()->create();
        $verifiedAt = now()->subDay();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'kyc_status' => 'verified',
            'kyc_verified_at' => $verifiedAt,
        ]);

        $this->service->evaluate($profile);

        $profile->refresh();
        $this->assertSame('verified', $profile->kyc_status);
        $this->assertEquals($verifiedAt->format('Y-m-d H:i:s'), $profile->kyc_verified_at->format('Y-m-d H:i:s'));
    }
}

