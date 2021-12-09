<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class UpdateRequest extends FormRequest
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
            'name' => 'string|between:2,100',
            'email' => 'string|email|max:100|unique:users',
            'age' => 'integer|max:100',
            'profile_picture' => 'mimes:pdf,jpg,JPG,JPEG,jpeg,png|max:50000',
            'password' => 'string|confirmed|min:6',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }


    public function messages()
    {
        return [
            'name.string ' => 'Name should be string!',
            'name.between ' => 'Name size should be between 2 & 100!',
            'email.string' => 'Email should be string!',
            'email.unique' => 'Email already exists!',
            'email.max' => 'Max number of characters in email is 100!',
            'Age.integer' => 'Age should be integer value!',
            'Age.max' => 'Max number of characters in age is 100!',
            'profile_picture.mimes' => 'Picture is not in require format!',
            'profile_picture.max' => 'Picture require size is 50MB!',
            'password.string' => 'Password should be string!',
            'password.confirmed' => 'Please enter confirm password!'
        ];
    }
}
