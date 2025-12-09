<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Reportar un producto, perfil, ranch, etc.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reportable_type' => 'required|string|in:App\\Models\\Product,App\\Models\\Profile,App\\Models\\Ranch',
            'reportable_id' => 'required|integer',
            'report_type' => 'required|string|in:spam,inappropriate,fraud,fake_product,harassment,other',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            
            if (!$user || !$user->profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile not found'
                ], 404);
            }

            // Crear el reporte
            $report = Report::create([
                'reporter_id' => $user->profile->id,
                'reportable_type' => $request->reportable_type,
                'reportable_id' => $request->reportable_id,
                'report_type' => $request->report_type,
                'description' => $request->description,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report created successfully',
                'data' => $report
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
