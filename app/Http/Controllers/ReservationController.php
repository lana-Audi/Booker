<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function book(Request $request)
    {
        $user_id = Auth::id();

        try {
            $validatedData = $request->validate([
                'apartment_id' => 'required|integer|exists:apartments,id',
                'start_date'   => 'required|date|after_or_equal:today',
                'end_date'     => 'required|date|after_or_equal:start_date',
            ]);

            $conflict = Reservation::where('apartment_id', $validatedData['apartment_id'])
                ->where(function ($query) use ($validatedData) {
                    $query->whereBetween('start_date', [$validatedData['start_date'], $validatedData['end_date']])
                        ->orWhereBetween('end_date', [$validatedData['start_date'], $validatedData['end_date']])
                        ->orWhere(function ($q) use ($validatedData) {
                            $q->where('start_date', '<=', $validatedData['start_date'])
                                ->where('end_date', '>=', $validatedData['end_date']);
                        });
                })
                ->exists();

            if ($conflict) {
                return response()->json([
                    'success' => false,
                    'message' => 'This apartment is already booked for the selected dates.',
                ], 422);
            }

            $validatedData['user_id'] = $user_id;

            $reservation = Reservation::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Apartment booked successfully',
                'data' => [
                    'reservation' => $reservation,
                ]
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function cancelReservation($id)
    {
        try {
            $user_id = Auth::id();

            $reservation = Reservation::where('id', $id)
                ->where('user_id', $user_id)
                ->first();

            if (!$reservation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found or you are not authorized to cancel it.',
                ], 404);
            }

            if ($reservation->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This reservation is already cancelled.',
                ], 422);
            }

            $reservation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully.',
                'data' => [
                    'reservation' => $reservation,
                    'cancelled_at' => now(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function getUserReservations()
    {
        try {
            $user_id = Auth::id();


            $activeReservations = Reservation::where('user_id', $user_id)
                ->whereNull('deleted_at')
                ->get();

            $cancelledReservations = Reservation::where('user_id', $user_id)
                ->onlyTrashed()
                ->get();

            $allReservations = Reservation::where('user_id', $user_id)
                ->withTrashed()
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'active_reservations' => $activeReservations,
                    'cancelled_reservations' => $cancelledReservations,
                    'all_reservations' => $allReservations,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve reservations',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function update_book(Request $request, $id)
    {
        try {
            $user_id = Auth::id();

            // البحث عن الحجز مع Soft Delete
            $book = Reservation::withTrashed()
                ->where('id', $id) // تأكد إنه 'id' مش 'reservation_id'
                ->where('user_id', $user_id) // تحقق من ملكية المستخدم
                ->first();

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reservation not found or you are not authorized to update it.',
                ], 404);
            }

            if ($book->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update a cancelled reservation.',
                ], 422);
            }


            $validatedData = $request->validate([
                'apartment_id' => 'sometimes|integer|exists:apartments,id',
                'start_date'   => 'sometimes|date|after_or_equal:today',
                'end_date'     => 'sometimes|date|after_or_equal:start_date',
            ]);

            if (isset($validatedData['apartment_id']) || isset($validatedData['start_date']) || isset($validatedData['end_date'])) {
                $apartment_id = $validatedData['apartment_id'] ?? $book->apartment_id;
                $start_date = $validatedData['start_date'] ?? $book->start_date;
                $end_date = $validatedData['end_date'] ?? $book->end_date;

                $conflict = Reservation::where('apartment_id', $apartment_id)
                    ->where('id', '!=', $id)
                    ->where(function ($query) use ($start_date, $end_date) {
                        $query->whereBetween('start_date', [$start_date, $end_date])
                            ->orWhereBetween('end_date', [$start_date, $end_date])
                            ->orWhere(function ($q) use ($start_date, $end_date) {
                                $q->where('start_date', '<=', $start_date)
                                    ->where('end_date', '>=', $end_date);
                            });
                    })
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This apartment is already booked for the selected dates.',
                    ], 422);
                }
            }


            $book->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Reservation updated successfully',
                'data' => $book
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
