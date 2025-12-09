<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Ranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KycApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_kyc_status_requires_authentication(): void
    {
        $this->getJson('/api/kyc/status')->assertStatus(401);
    }

    public function test_can_get_default_kyc_status_for_profile(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $res = $this->getJson('/api/kyc/status');
        $res->assertOk()
            ->assertJson([
                'kyc_status' => 'no_verified',
            ]);
    }

    public function test_full_kyc_flow_sets_status_to_verified_automatically(): void
    {
        // Mock Gemini response para que siempre apruebe en el test
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
                                        'face_analysis' => [
                                            'selfie_has_face' => true,
                                            'selfie_with_doc_has_face' => true,
                                            'selfie_with_doc_has_document' => true,
                                            'faces_match' => 'yes',
                                            'face_match_confidence' => 'high',
                                        ],
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
            'kyc_status' => 'no_verified',
        ]);

        // Crear ranch principal con RIF válido para cumplir condición KYC
        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'name' => 'Hacienda KYC',
            'is_primary' => true,
            'tax_id' => 'J-12345678-9',
        ]);

        Sanctum::actingAs($user);

        // Iniciar KYC
        $this->postJson('/api/kyc/start', [
            'document_type' => 'ci_ve',
            'country_code' => 'VE',
        ])->assertOk();

        // Subir documento CI y RIF
        $front = UploadedFile::fake()->image('front.jpg', 800, 600);
        $rif = UploadedFile::fake()->image('rif.jpg', 800, 600);

        $this->postJson('/api/kyc/upload-document', [
            'front' => $front,
            'rif' => $rif,
        ])->assertOk();

        // Subir selfie
        $selfie = UploadedFile::fake()->image('selfie.jpg', 800, 800);

        $this->postJson('/api/kyc/upload-selfie', [
            'selfie' => $selfie,
        ])->assertOk();

        // Subir selfie con documento
        $selfieWithDoc = UploadedFile::fake()->image('selfie_doc.jpg', 800, 800);

        $this->postJson('/api/kyc/upload-selfie-with-doc', [
            'selfie_with_doc' => $selfieWithDoc,
        ])->assertOk();

        // Verificar estado final
        $profile->refresh();

        $this->assertSame('verified', $profile->kyc_status);
        $this->assertNotNull($profile->kyc_verified_at);
        $this->assertNotNull($profile->kyc_doc_front_path);
        $this->assertNotNull($profile->kyc_rif_path);
        $this->assertNotNull($profile->kyc_selfie_path);
        $this->assertNotNull($profile->kyc_selfie_with_doc_path);
    }
}


