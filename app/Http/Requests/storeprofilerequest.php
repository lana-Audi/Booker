<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class storeprofilerequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

     
    public function rules(): array
    {
     
        return [
            'phone_number'=>'string|max:15',
            'personal_image'=>'required|image|mimes:png,jpg,jpeg,gif|max:2048',
            'id_image'=>'required|image|mimes:png,jpg,jpeg,gif|max:2048',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'date_of_birth' => 'required',

        ];
}
}