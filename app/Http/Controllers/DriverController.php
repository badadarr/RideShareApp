<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    public function show(Request $request)
    {
        $user = request()->user();
        $user->load('driver');
        return $user;
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'year' => 'required|numeric|between:1900,2022',
                'color' => 'required',
                'make' => 'required',
                'model' => 'required',
                'license_plate' => 'required|alpha_num',
                'name' => 'required',
            ]);

            $user = request()->user();
            $user->update($request->only('name'));

            // create or update a driver associated with this user
            $user->driver()->updateOrCreate($request->only('color', 'year', 'make', 'model', 'license_plate'));
            $user->load('driver');
            return $user;

        } catch (\Exception $e) {
            Log::error('Validation error: ' . $e->getMessage());
            return response()->json(['message' => 'Kesalahan Validasi'], 401);
        }
    }
}
