<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'age' => 'required|integer|max:100',
            'profile_picture' => 'string',
            // 'profile_picture' => 'mimes:pdf,jpg,jpeg,png',
            'password' => 'required|string|confirmed|min:6',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => $validator->errors()
        ]));
    }


    public function messages()
    {
        return [
            'name.required' => 'Name is required!',
            'email.required' => 'Email is required!',
            'Age.required' => 'Age is required!',
            'profile_picture.mimes' => 'Picture should be in base 64 required string',
            // 'profile_picture.mimes' => 'Picture is not in require format!',
            'password.required' => 'Password is required!'
        ];
    }
}
