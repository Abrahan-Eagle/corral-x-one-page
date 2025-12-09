<?php

namespace App\Http\Controllers\Insights;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Services\Insights\IAInsightsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IAInsightsController extends Controller
{
    public function __construct(private readonly IAInsightsService $service)
    {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $timeRange = $request->query('time_range', '7d');
        $role = $request->query('role');

        $data = $this->service->generateDashboard($user, $timeRange, $role);

        return response()->json($data);
    }

    public function updateRecommendationStatus(Request $request, string $recommendationKey): JsonResponse
    {
        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $result = $this->service->updateRecommendationStatus(
            $request->user(),
            $recommendationKey,
            (bool) $validated['is_completed']
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function updateUserLevel(Request $request, User $user): JsonResponse
    {
        $actingUser = $request->user();
        if (!$actingUser || strtolower((string) $actingUser->role) !== 'admin') {
            abort(403, 'Solo administradores pueden actualizar los niveles de IA Insights.');
        }

        $validated = $request->validate([
            'level' => ['required', 'in:free,premium,admin'],
        ]);

        $level = $validated['level'];

        $profile = Profile::where('user_id', $user->id)->first();
        Log::debug('IAInsightsController:updateUserLevel - initial profile lookup', [
            'target_user' => $user->id,
            'level' => $level,
            'profile_found' => (bool) $profile,
        ]);

        if (!$profile && $level !== 'admin') {
            $fullName = trim((string) $user->name);
            $nameParts = array_values(array_filter(explode(' ', $fullName)));
            $firstName = $user->given_name ?? ($nameParts[0] ?? 'Pendiente');
            $lastName = $user->family_name ?? ($nameParts[1] ?? 'Completar');

            $profile = new Profile([
                'user_id' => $user->id,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'status' => 'incompleteData',
                'user_type' => 'both',
                'is_premium_seller' => $level === 'premium',
                'accepts_calls' => true,
                'accepts_whatsapp' => true,
                'accepts_emails' => true,
                'ci_number' => sprintf(
                    'TMP-%d-%s',
                    $user->id,
                    Str::upper(Str::random(5))
                ),
            ]);
            $profile->save();
            Log::debug('IAInsightsController:updateUserLevel - placeholder profile created', [
                'profile_id' => $profile->id,
                'ci_number' => $profile->ci_number,
            ]);
        }

        DB::transaction(function () use ($level, $user, $profile) {
            if ($level === 'admin') {
                $user->role = 'admin';
                $user->save();

                if ($profile) {
                    $profile->is_premium_seller = false;
                    $profile->premium_expires_at = null;
                    $profile->save();
                }

                return;
            }

            // Niveles Free o Premium comparten rol "users"
            $user->role = 'users';
            $user->save();

            if ($profile) {
                $profile->is_premium_seller = $level === 'premium';
                if ($level !== 'premium') {
                    $profile->premium_expires_at = null;
                }
                Log::debug('IAInsightsController:updateUserLevel - saving profile', [
                    'profile_id' => $profile->id,
                    'is_premium_seller' => $profile->is_premium_seller,
                ]);
                $profile->save();
            }
        });

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'level' => $level,
            ],
        ]);
    }
}

