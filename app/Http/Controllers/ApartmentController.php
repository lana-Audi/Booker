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
            'location' => 'required|string|max:255',
            'rent_price' => 'integer|min:0',
            'sale_price' => 'integer|min:0',
            'apartment_area' => 'integer|min:0',
        ]);

        $apartment = Apartment::create([
            'location' => $validatedData['location'],
            'rent_price' => $validatedData['rent_price']?? null,
            'sale_price' => $validatedData['sale_price'] ?? null,
            'apartment_area' => $validatedData['apartment_area'] ,
        ]);

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
         //   'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Creation failed',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function index ()
    {
       $tasks=Apartment::all();
       return response()->json($tasks,200);
    
    }
}
