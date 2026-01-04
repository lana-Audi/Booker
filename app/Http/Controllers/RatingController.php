<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    private $apartmentController;

    private function canUserReviewApartment($userId, $reservationId)
    {
        $booking = Reservation::find($reservationId);
        
        if (!$booking) {
            return false;
        }
        
        // تحقق اذا كان في زلمة حاجز
        if ($booking->user_id != $userId) {
            return false;
        }
        
        
        
        // //  ما بيقدر يحجز اذا قبل ما يخلص حجزه التحقق من تاريخ الحجز 
        // $today = Carbon::now()->toDateString();//تاريخ التقييم 
        // if ($booking->end_date > $today) {
        //     return false;
        // }
        
        // اذا قي تقييم سابق 
        $existingReview = Rating::where('booking_id', $reservationId)->first();
        if ($existingReview) {
            return false;
        }
        
        return true;
    }
    
   //اضافة تقييم
    public function create($reservationId)
    {
        $userId = Auth::id();
        
        if (!$this->canUserReviewApartment($userId, $reservationId)) {
            return back()->with('error', 'لا يمكنك تقييم هذه الشقة');
        }
        
        $booking = Reservation::with('apartment')->find($reservationId);
        
        return view('ratings.create', compact('booking'));
    }
    
   
    public function store(Request $request, $reservationId)
    {
        $userId = Auth::id();
        
        if (!$this->canUserReviewApartment($userId, $reservationId)) {
            return back()->with('error', 'لا يمكنك إضافة تقييم');
        }
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',

        ]);
        $validated['user_id'] = $userId;
        // حفظ التقييم
        $rate = Rating::create([
            'booking_id' => $reservationId,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'user_id' =>$validated['user_id'],
        ]);
        
        $this->apartmentController->updateApartmentRating($reservationId);
        
        return redirect()
            ->route('bookings.index')
            ->with('success', 'تم إضافة التقييم بنجاح');
    }
    
    }


