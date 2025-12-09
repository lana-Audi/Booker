<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeprofilerequest;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{

    public function store(storeprofilerequest $request)
    {
        $user_id = Auth::user()->id;
        $validatedData = $request->validated();
        $validatedData['user_id'] = $user_id;
        if ($request->hasFile('image'))
        {
         $path=$request->file('image')->store('my photo ','public');
         $validatedData['image'];
        }
        $profile = Profile::create($validatedData);
        return response()->json([$profile], 201);
    }

    public function update(Request $request,)
    {
        $user_id = Auth::user()->id;
        $profile = Profile::findOrFail($user_id);

        if ($profile->user_id != $user_id) {
            return response()->json(['message' => 'unauthurized'], 403);
        }

        //$task->update($request->all()); ----> that's denger because he can edit any thing-->Hacking
        $profile->update($request->only('bio', 'date_of_birth', 'phone'));
        return response()->json($profile, 200);
    }


    public function show()
    {
        $user_id = Auth::user()->id;
        $profile = Profile::where('user_id', $user_id)->firstOrFail();

        if ($profile->user_id != $user_id) {
            return response()->json(['message' => 'unauthurized'], 403);
        }

        return response()->json($profile, 200);
    }
}
