<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;

class UpdateProfilePhotosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Actualizando fotos de perfil con URLs reales...');

        // URLs de avatares reales que siempre funcionan (pravatar.cc)
        $realProfilePhotos = [
            'https://i.pravatar.cc/300?img=1',
            'https://i.pravatar.cc/300?img=2',
            'https://i.pravatar.cc/300?img=3',
            'https://i.pravatar.cc/300?img=4',
            'https://i.pravatar.cc/300?img=5',
            'https://i.pravatar.cc/300?img=6',
            'https://i.pravatar.cc/300?img=7',
            'https://i.pravatar.cc/300?img=8',
            'https://i.pravatar.cc/300?img=9',
            'https://i.pravatar.cc/300?img=10',
            'https://i.pravatar.cc/300?img=11',
            'https://i.pravatar.cc/300?img=12',
            'https://i.pravatar.cc/300?img=13',
            'https://i.pravatar.cc/300?img=14',
            'https://i.pravatar.cc/300?img=15',
        ];

        // Actualizar perfiles que tengan URLs de placeholder o Unsplash rotas
        $profilesWithPlaceholders = Profile::where('photo_users', 'like', '%via.placeholder.com%')
            ->orWhere('photo_users', 'like', '%placeholder.com%')
            ->orWhere('photo_users', 'like', '%placehold.it%')
            ->orWhere('photo_users', 'like', '%images.unsplash.com%')
            ->get();

        $updatedCount = 0;

        foreach ($profilesWithPlaceholders as $profile) {
            $profile->update([
                'photo_users' => fake()->randomElement($realProfilePhotos),
            ]);
            $updatedCount++;
        }

        $this->command->info("✅ {$updatedCount} fotos de perfil actualizadas con URLs reales");

        // Verificar algunas URLs actualizadas
        $sampleProfiles = Profile::whereNotNull('photo_users')->take(3)->get(['photo_users']);
        $this->command->info('📸 URLs de muestra:');
        foreach ($sampleProfiles as $profile) {
            $this->command->line("   - {$profile->photo_users}");
        }
    }
}
