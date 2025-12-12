<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function pendingUsers()
{
    return User::where('is_approved', false)->get();
}

public function approveUser($id)
{
    $user = User::findOrFail($id);
    $user->update(['is_approved' => true]);

    return response()->json([
        'success' => true,
        'message' => 'User approved successfully'
    ]);
}

}
