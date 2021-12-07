<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Service\jwtService;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Requests\UserStoreRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;
class AuthController extends Controller
{

    public function register(UserStoreRequest $request)
    {
        try {
            $validator=$request;
            $data=$validator->validated();
            if($request->hasFile('profile_picture'))
            {
                $pic = $request->profile_picture;
                // dd($pic);
                $allowedfileExtension=['pdf','jpg','png','jpeg'];
                // $files = $request->profile_picture;
                // $errors = [];

            // foreach ($pic as $file) {
                // dd($pic);
                $extension = $pic->getClientOriginalExtension();

                $check = in_array($extension,$allowedfileExtension);
                if($check) {
                    // foreach($request->fileName as $mediaFiles) {

                        $path = $pic->store('public/profile');
                        // $imageName = $pic->getClientOriginalName();
                    // }
                } else {
                    throw new Exception('invalid_file_format');
                }
            // }

                // $base64_str = substr($pic, strpos($pic, ",")+1);
                // //decode base64 string
                // $image = base64_decode($base64_str);
                // $imageName = Str::random(10) . '.jpg';
                // Storage::disk('local')->put($imageName, $image);

                // dd($path);
                $data=array_merge(
                    $data,
                    ['profile_picture' =>$path]
                );
            }
            $user = User::create(array_merge(
                        $data,
                        ['password' =>Hash::make($request->password)]
                    ));
            // $mail=[
            //     'name'=>$request->name,
            //     'info'=>'Press the following link to verify your account',
            //     'Verification_link'=>url('api/user/verifyMail/'.$request->email)
            // ];
            // $jwt=(new jwtService)->gettokenencode($validator->validated());
            // \Mail::to($request->email)->send(new \App\Mail\NewMail($mail));
            // dispatch(new \App\Jobs\SendEmailVerify($request->email,$mail));
            return response()->success([
                'message' => 'User successfully registered',
                // 'token'=>$jwt,
                'user' => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            return response()->error($e->getMessage(),203);
        }

    }

    public function login(Request $request){

            try {
                $validator = Validator::make($request->all(), [
                    'email' => 'required|email',
                    'password' => 'required|string|min:6',
                ]);

                if ($validator->fails()) {
                    return response()->json($validator->errors(), 422);
                }
                $user=$validator->validated();
                $authenticate=User::query();
                $authenticate=$authenticate->where('email',$user['email'])->first();
                if(isset($authenticate))
                {

                    // if($authenticate->verify==1)
                    // {
                        if (Hash::check($user['password'], $authenticate->password)) {
                            $data['name']=$authenticate->name;
                            $data['email']=$authenticate->email;
                            $data['password']=$user['password'];
                            $jwt=(new jwtService)->gettokenencode($data);
                            $authenticate->remember_token=$jwt;
                            $authenticate->save();
                        }
                        else
                        {
                            throw new Exception('Unauthorized');
                        }
                    // }
                    // else
                    // {
                    //     throw new Exception('Please verify the link first');
                    // }

                }
                else
                {
                    throw new Exception('Unauthorized');
                }

                return response()->success([
                    'message' => 'User successfully login',
                    'user' => new UserResource($authenticate)
                ], 201);
            } catch (Exception $e) {
                if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                    return response()->error('Token is Invalid',401);
                }else if ($e instanceof \Firebase\JWT\ExpiredException){
                    return response()->error('Token is Expired',401);
                }else{
                    return response()->error($e->getMessage(),401);
                }
            }

        return response()->error([
            'message' => 'login unsuccessfull. Make Sure input is given',
        ], 201);

    }

    public function logout(Request $request){


        }
}
