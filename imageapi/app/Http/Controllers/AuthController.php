<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Service\jwtService;
use App\Models\User;
use App\Models\Password_reset;
use Illuminate\Support\Str;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;
class AuthController extends Controller
{

    public function register(UserStoreRequest $request)
    {
        try {
            $validator=$request;
            $data=$validator->validated();
            if($request->has('profile_picture'))
            {

                $image = $request->profile_picture;
                $pos  = strpos($image, ';');
                $type = explode(':', substr($image, 0, $pos))[1];
                $ext=explode('/',$type);
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imagefile = time().rand().'.'.$ext[1];
                $path = public_path().'//storage//profile//'.$imagefile;
                $url=url('storage/profile/'.$imagefile);

                $allowedfileExtension=['pdf','jpg','png','jpeg'];
                $check = in_array($ext[1],$allowedfileExtension);
                if($check)
                {
                file_put_contents($path,base64_decode($image));
                }
                else{
                    throw new Exception('Invalid image format');
                }

                $data=array_merge(
                    $data,
                    ['profile_picture' =>$path,'profile_picture_url' =>$url]
                );
            }
            else{
                $path = 'public/profile/default/user.png';
                $url = url('/storage/profile/default/user.png');
                $data=array_merge(
                    $data,
                    ['profile_picture' =>$path,'profile_picture_url' =>$url]
                );
            }

            $user = User::create(array_merge(
                        $data,
                        ['password' =>Hash::make($request->password)]
                    ));
            $mail=[
                'name'=>$request->name,
                'info'=>'Press the following link to verify your account',
                'Verification_link'=>url('api/user/verifyMail/'.$request->email)
            ];
            dispatch(new \App\Jobs\SendEmailVerify($request->email,$mail));
            return response()->success([
                'message' => 'User successfully registered',
                'user' => new UserResource($user)
            ], 201);
        } catch (Exception $e) {
            return response()->error([$e->getMessage(),$e->getLine()],203);
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

                    if($authenticate->verify==1)
                    {
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
                    }
                    else
                    {
                        throw new Exception('Please verify the link first');
                    }

                }
                else
                {
                    throw new Exception('Unauthorized');
                }

                return response()->success([
                    'message' => 'User successfully login',
                    'user' => new UserResource($authenticate)
                ], 200);
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


    public function forgetPassword(Request $request)
    {
        try{
            if(User::where("email",$request->email)->exists())
            {
                $resetPassword = new Password_reset();
                $resetPassword->email = $request->email;
                $resetPassword->token = Str::random(10);
                $resetPassword->save();
                $data = ['Verification_link'=>url('api/auth/'.$resetPassword->email.'/'.$resetPassword->token)];
                \Mail::to($request->email)->send(new \App\Mail\ForgetMail($data));
                // dispatch(new \App\Jobs\SendForgotEmail($request->email,$data));
                return response()->success("Password reset mail has been sent",200);
            }
            else
            {
                throw new Exception("Email Does not exist");
            }
        }
        catch(Exception $e)
        {
            return response()->error($e->getMessage(),500);
        }
    }

    public function updatepassword(Request $request,$email,$token)
    {
        try{
        if(Password_reset::where('token',$token)->exists())
        {
            $deleteToken = Password_reset::where('token',$token)->first();
            $deleteToken->delete();
            // $validated = $request->validated();
            $user = User::where('email',$email)->first();
            $validated['password'] = bcrypt($request->password);
            $user->password =$request->password;
            $user->save();

            return response()->success("Password Updated",200);
        }
        else
        {
            return response()->error("Unauthorized",404);
        }
           }
           catch(Exception $e)
           {
               return response()->error($e->getMessage(),404);
           }
    }
}
