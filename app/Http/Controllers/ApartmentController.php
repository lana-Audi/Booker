<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Rating;
use App\Models\Reservation;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ApartmentController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'city'              => 'required|string|max:255',
                'Governorate'       => 'required|string|max:255',
                'rent_price'        => 'required|integer|min:0',
                'apartment_space'   => 'required|integer|min:0',
                'rooms'             => 'nullable|integer|min:0',
                'floor'             => 'nullable|integer|min:0',
                'bathrooms'         => 'nullable|integer|min:0',
                'apartment_image'   => 'nullable|image|mimes:png,jpg,jpeg,gif|max:2048', // أضف nullable
            ]);

            // معالجة الصورة بشكل صحيح
            if ($request->hasFile('apartment_image')) {
                $imagePath = $request->file('apartment_image')->store('apartments', 'public');
                $validatedData['apartment_image'] = $imagePath; // أو 'image' حسب اسم الحقل في DB
            } else {
                $validatedData['apartment_image'] = null; // تعيين قيمة افتراضية
            }

            // للتأكد من أن البيانات تحتوي على الحقول الصحيحة
            $apartment = Apartment::create([
                'city' => $validatedData['city'],
                'Governorate' => $validatedData['Governorate'],
                'rent_price' => $validatedData['rent_price'],
                'apartment_space' => $validatedData['apartment_space'],
                'rooms' => $validatedData['rooms'] ?? null,
                'floor' => $validatedData['floor'] ?? null,
                'bathrooms' => $validatedData['bathrooms'] ?? null,
                'apartment_image' => $validatedData['apartment_image'],
                // أو استخدم: 'apartment_image' => $validatedData['apartment_image']
            ]);

            $apartment->users()->attach(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Apartment created successfully',
                'data' => [
                    'apartment' => $apartment,
                    'image_url' => $apartment->image ? asset('storage/' . $apartment->image) : null
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
    }public function index()
    {
        $apartments = Apartment::all();

        if ($apartments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد شقق متاحة حالياً',
                'data' => []
            ], 404);
        }

        // تعديل الصور
       $formatted = $apartments->map(function ($apartment) {
    return [
        'id' => $apartment->id,
        'city' => $apartment->city,
        'Governorate' => $apartment->Governorate,
        'rent_price' => $apartment->rent_price,
        'apartment_space' => $apartment->apartment_space,
        'rooms' => $apartment->rooms,
        'floor' => $apartment->floor,
        'bathrooms' => $apartment->bathrooms,
        'apartment_image' => $apartment->apartment_image
            ? "http://10.0.2.2:8000/storage/" . $apartment->apartment_image
            : null,
    ];
});

        return response()->json([
            'success' => true,
            'data' => $formatted
        ], 200);
    }
    public function filter(Request $request)
    {
        try {
            $query = Apartment::query();

            //حسب الغرف

            if ($request->has('rooms') && !empty($request->rooms)) {
                if (is_numeric($request->rooms)) {
                    $query->where('rooms', $request->rooms);
                } elseif (is_array($request->rooms)) {
                    $query->whereIn('rooms', $request->rooms);
                }
            }

            //  فلترة الحمامات
            if ($request->has('bathrooms') && !empty($request->bathrooms)) {
                $query->where('bathrooms', $request->bathrooms);
            }

            //فلترة حسب  السعر 
            if ($request->has('min_price') && $request->min_price > 0) {
                $query->where('rent_price', '>=', $request->min_price);
            }

            if ($request->has('max_price') && $request->max_price > 0) {
                $query->where('rent_price', '<=', $request->max_price);
            }


            //حسب المحافظة
            if ($request->filled('Governorate')) {
                $query->where('Governorate', $request->governorate);
            }

            // حسب المنطقة
            if ($request->filled('city')) {
                $query->where('city', $request->city);
            }

            //حسب المساحة
            if ($request->has('min_space')) {
                $query->where('apartment_space', '>=', $request->min_space);
            }

            if ($request->has('max_space')) {
                $query->where('apartment_space', '<=', $request->max_space);
            }


            // الحصول على النتائج
            $apartments = $query->get();

            return response()->json([
                'success' => true,
                'data' => $apartments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في التصفية',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function show($id)
{
    $apartment = Apartment::with(['images', 'facilities'])->findOrFail($id);
    
    $reviews = Rating::whereHas('booking', function($query) use ($id) {
        $query->where('apartment_id', $id);
    })
    ->with(['user', 'booking'])
    ->orderBy('created_at', 'desc')
    ->paginate(10);
    
    return view('apartments.show', compact('apartment', 'reviews'));
}

    
public function updateApartmentRating($reservationId)
{
    $booking = Reservation::with('apartment')->find($reservationId);//راح جاب المعلومات من قاعدة البيانات
    $apartment = $booking->apartment;
    
    //للتقييمات حساب المتوسط
    $avgRating = Rating::join('reservations', 'ratings.booking_id', '=', 'reservations.id')
        ->where('reservations.apartment_id', $apartment->id)
        ->avg('ratings.rating');
        
    $apartment->update([
        'average_rating' => round($avgRating, 1),
        'total_reviews' => Rating::join('reservations', 'ratings.booking_id', '=', 'reservations.id')
            ->where('reservations.apartment_id', $apartment->id)
            ->count()
    ]);
}
}