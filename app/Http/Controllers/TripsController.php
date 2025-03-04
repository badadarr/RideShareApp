<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TripsController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'origin' => 'required',
                'destination' => 'required',
                'destination_name' => 'required',
            ]);

            return $request->user()->trips()->create($request->only('origin', 'destination', 'destination_name'));
        } catch (\Exception $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }

    public function show(Request $request, Trip $trip)
    {
        try {

            // is the trip is associated with the authenticated user?
            if ($trip->user->id === $request->user()->id) {
                return $trip;
            }

            if ($trip->driver && $request->user()->driver) {
                if ($trip->driver->id === $request->user()->driver->id) {
                    return $trip;
                }
            }

            return response()->json(['message' => 'Tidak ditemukan trip yang di tuju'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }

    public function accept(Request $request, Trip $trip)
    {
        // driver menyetujui trip
        try {
            $request->validate([
                'driver_location' => 'required',
            ]);

            $trip->update([
                'driver_id' => $request->user()->id,
                'driver_location' => $request->driver_location,
            ]);

            $trip->load('driver.user');

            TripAccepted::dispatch($trip, $request->user());

            return $trip;

        } catch (\Exception $e) {
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }

    public function start(Request $request, Trip $trip)
    {
        // driver memulai menjemput penumpang menuju lokasi
        try {
            $trip->update([
                'is_started' => true,
            ]);

            $trip->load('driver.user');

            TripStarted::dispatch($trip, $trip->user);

            return $trip;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }

    public function end(Request $request, Trip $trip)
    {
        // driver mengakhiri trip
        try {
            $trip->update([
                'is_completed' => true,
            ]);

            $trip->load('driver.user');

            TripEnded::dispatch($trip, $trip->user);

            return $trip;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }

    public function location(Request $request, Trip $trip)
    {
        // driver mengupdate lokasi
        try {
            $request->validate([
                'driver_location' => 'required',
            ]);
            $trip->update([
                'driver_location' => $request->driver_location,
            ]);

            $trip->load('driver.user');

            TripLocationUpdated::dispatch($trip, $trip->user);

            return $trip;
        } catch (\Exception $e) {
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }
}
