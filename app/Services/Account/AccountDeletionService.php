<?php

namespace App\Services\Account;

use App\Models\Message;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountDeletionService
{
    /**
     * Elimina de forma permanente al usuario autenticado
     * junto con su perfil, haciendas, productos y archivos.
     */
    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $profile = $user->profile()
                ->with([
                    'ranches.documents',
                    'ranches.products.images',
                ])
                ->first();

            if ($profile) {
                $this->deleteProfileMedia($profile);
                $this->deleteMessageAttachments($profile->id);
            }

            // Tablas auxiliares que referencian directamente a users.* (sin FK declarada)
            if (Schema::hasTable('favorite_ranches')) {
                DB::table('favorite_ranches')
                    ->where('user_id', $user->id)
                    ->delete();
            }

            $user->tokens()->delete();
            $user->forceDelete();
        });
    }

    /**
     * Elimina archivos asociados al perfil y sus relaciones.
     */
    protected function deleteProfileMedia(Profile $profile): void
    {
        $this->deletePublicFile($profile->photo_users);

        foreach ($profile->ranches as $ranch) {
            $this->deleteRanchMedia($ranch);
        }
    }

    /**
     * Elimina archivos relacionados a una hacienda.
     */
    protected function deleteRanchMedia(Ranch $ranch): void
    {
        $this->deletePublicFile($ranch->business_license_url);

        foreach ($ranch->documents as $document) {
            $this->deletePublicFile($document->document_url);
        }

        foreach ($ranch->products as $product) {
            $this->deletePublicFile($product->health_certificate_url);

            foreach ($product->images as $image) {
                $this->deletePublicFile($image->file_url);
            }
        }
    }

    /**
     * Elimina archivos adjuntos de chats enviados por el perfil.
     */
    protected function deleteMessageAttachments(int $profileId): void
    {
        Message::where('sender_id', $profileId)
            ->whereNotNull('attachment_url')
            ->chunkById(200, function ($messages) {
                foreach ($messages as $message) {
                    $this->deletePublicFile($message->attachment_url);
                }
            });
    }

    /**
     * Elimina un archivo del storage público si existe.
     */
    protected function deletePublicFile(?string $url): void
    {
        $relativePath = $this->extractStoragePath($url);

        if (!$relativePath) {
            return;
        }

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    /**
     * Convierte una URL completa en una ruta relativa dentro de storage/app/public.
     */
    protected function extractStoragePath(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $baseUrls = array_filter([
            config('app.url'),
            env('APP_URL_PRODUCTION'),
            env('APP_URL_LOCAL'),
        ]);

        foreach ($baseUrls as $baseUrl) {
            $baseUrl = rtrim($baseUrl, '/');
            $storagePrefix = $baseUrl . '/storage/';

            if (Str::startsWith($url, $storagePrefix)) {
                return Str::after($url, $storagePrefix);
            }
        }

        if (Str::startsWith($url, 'storage/')) {
            return Str::after($url, 'storage/');
        }

        // Si ya es una ruta relativa sin protocolo
        if (!Str::contains($url, '://')) {
            return ltrim($url, '/');
        }

        return null;
    }
}


