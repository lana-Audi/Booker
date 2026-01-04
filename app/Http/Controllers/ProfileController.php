<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeprofilerequest;
use App\Models\Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


use function Pest\Laravel\delete;

class ProfileController extends Controller
{
    public function store(StoreProfileRequest $request)
    {
        $user_id = Auth::id();

        $validatedData = $request->validated(); //انا هون بالريكوست ماعندي اي دي 
        $validatedData['user_id'] = $user_id; //دخلت الاي دي عن طريق التوكن 
        
        //الصور عم قله اذا كان في بالريكوست صورى هيك اسمها 
        if ($request->hasFile('personal_image')) {
            $validatedData['personal_image'] = $request->file('personal_image')->store('personal_images', 'public');
        }

        if ($request->hasFile('id_image')) {
            $validatedData['id_image'] = $request->file('id_image')->store('id_images', 'public');
        }


        $profile = Profile::create($validatedData);
        return response()->json($profile, 201);
    }


    public function update(StoreProfileRequest $request)
    {
        $user_id = Auth::id();
        $profile = Profile::where('user_id', $user_id)->firstOrFail();

        if ($profile->user_id != $user_id) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $profile->update($request->only('date_of_birth', 'first_name', 'last_name'));

         
        if ($request->hasFile('personal_image')) {
  
            if ($profile->personal_image) {
                Storage::disk('public')->delete($profile->personal_image);
            }

            $profile->personal_image = $request->file('personal_image')->store('personal_images', 'public');
        }

        if ($request->hasFile('id_image')) {
            if ($profile->id_image) {
                Storage::disk('public')->delete($profile->id_image);
            }
            $profile->id_image = $request->file('id_image')->store('id_images', 'public');
        }

        $profile->save();

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
