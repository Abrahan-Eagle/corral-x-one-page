<?php

namespace App\Http\Controllers\Profiles;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Profile;
use App\Models\State;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Display a listing of the addresses.
     */
    public function index()
    {
        // Obtener todas las direcciones
        $addresses = Address::with(['profile', 'city'])->get();
        return response()->json($addresses);
    }

    /**
     * Store a newly created address in storage.
     */
public function store(Request $request)
{
    // Validar los datos de la solicitud
    $validator = Validator::make($request->all(), [
        'profile_id' => 'required|exists:profiles,id', // ✅ Cambiado: validar que existe en profiles.id (no user_id)
        'adressses' => 'required|string|max:255',
        'latitude' => 'required|numeric',
        'longitude' => 'required|numeric',
        // 'status' => 'required|in:completeData,incompleteData,notverified',
        'city_id' => 'required|exists:cities,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => $validator->errors(),
            'message' => 'Error de validación. Por favor, revise los datos ingresados.'
        ], 400);
    }

    // ✅ Buscar el perfil por su ID directamente (el frontend envía profile.id)
    $profile = Profile::findOrFail($request->profile_id);
    
    // ✅ Verificar que el perfil pertenezca al usuario autenticado (seguridad)
    if (Auth::check()) {
        $user = Auth::user();
        if ($profile->user_id != $user->id) {
            return response()->json([
                'error' => ['profile_id' => ['El perfil no pertenece al usuario autenticado.']]
            ], 403);
        }
    }

    // Verificar si ya existe una dirección SOLO para level='users' (dirección personal única)
    $level = $request->input('level', 'users');
    
    if ($level === 'users') {
        // Solo UNA dirección personal por perfil
        $existingAddress = Address::where('profile_id', $profile->id)
            ->where('level', 'users')
            ->first();

        if ($existingAddress) {
            return response()->json([
                'message' => 'Ya tiene una dirección personal guardada',
                'existing_address_id' => $existingAddress->id
            ], 409); // 409 Conflict
        }
    }
    // Para level='ranches' o 'cattle', permitir múltiples direcciones (una por hacienda)

    $statusx = 'notverified';

    // Crear una nueva dirección
    $address = Address::create([
        'adressses' => $request->adressses,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'status' => $statusx,
        'profile_id' => $profile->id,
        'city_id' => $request->city_id,
        'level' => $level, // users, ranches, cattle
        'parish_id' => $request->input('parish_id'), // opcional
    ]);

    return response()->json(['message' => 'Address created successfully', 'address' => $address], 201);
}



    /**
     * Display the specified address.
     */
    public function show($id)
    {
        $profile = Profile::where('user_id', $id)->firstOrFail();

        $addresses = Address::where('profile_id', $profile->id)->get();

        if ($addresses->isEmpty()) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        return response()->json($addresses);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, $id)
    {
        // Buscar la dirección por ID
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        // Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'adressses' => 'string|max:255',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'status' => 'in:completeData,incompleteData,notverified',
            'profile_id' => 'exists:profiles,id',
            'city_id' => 'exists:cities,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Actualizar la dirección
        $address->adressses = $request->adressses ?? $address->adressses;
        $address->latitude = $request->latitude ?? $address->latitude;
        $address->longitude = $request->longitude ?? $address->longitude;
        $address->status = $request->status ?? $address->status;
        $address->profile_id = $request->profile_id ?? $address->profile_id;
        $address->city_id = $request->city_id ?? $address->city_id;

        // Guardar los cambios
        $address->save();

        return response()->json(['message' => 'Address updated successfully', 'address' => $address]);
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy($id)
    {
        // Buscar la dirección por ID
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        // Eliminar la dirección
        $address->delete();

        return response()->json(['message' => 'Address deleted successfully']);
    }



public function getCountries(Request $request)
{
    $countries = Country::get(['name', 'id']);
    Log::info('Países recuperados: ', $countries->toArray());
    return response()->json($countries);
}

public function getState(Request $request)
{
    $request->validate([
        'countries_id' => 'required|exists:countries,id', // Validación
    ]);

    $states = State::where("countries_id", $request->countries_id)->get(["name", "id"]);
    // return response()->json(['states' => $states]);
    return response()->json($states);
}

public function getCity(Request $request)
{
    $request->validate([
        'state_id' => 'required|exists:states,id', // Validación
    ]);

    $cities = City::where("state_id", $request->state_id)->get(["name", "id"]);
    return response()->json($cities);
}

public function getParishes(Request $request)
{
    $request->validate([
        'city_id' => 'required|exists:cities,id', // Validación
    ]);

    $parishes = \App\Models\Parish::where("city_id", $request->city_id)->get(["name", "id"]);
    return response()->json($parishes);
}
}
