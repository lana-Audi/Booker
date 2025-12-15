<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'location'          => 'required|string|max:255',
                'rent_price'        => 'nullable|integer|min:0',
                'sale_price'        => 'nullable|integer|min:0',
                'apartment_space'    => 'required|integer|min:0',
                'rooms'             => 'nullable|integer|min:0',
                'floor'             => 'nullable|integer|min:0',
                'bathrooms'         => 'nullable|integer|min:0',
            ]);
    
            $apartment = Apartment::create($validatedData);
    
            return response()->json([
                'success' => true,
                'message' => 'Apartment created successfully',
                'data' => [
                    'apartment' => $apartment,
                ]
            ], 201);
    
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
            ], 422);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
public function index()
{
    $tasks = Apartment::all();

    if ($tasks->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'لا توجد شقق متاحة حالياً',
            'data' => []
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $tasks
    ], 200);
}

}
