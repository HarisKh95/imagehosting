<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailRequest;
use App\Models\User;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateRequest;
use Illuminate\Support\Facades\File;
use App\Service\jwtService;
use Illuminate\Support\Facades\Storage;
use Exception;

class UserController extends Controller
{

    public function getUser(Request $request)
    {
        try {
            $user=User::where('email',$request->data['email'])->first();
            if(isset($user))
            {
                return response()->success([
                    'user' => new UserResource($user)
                ], 201);

            }
            else{
                throw new Exception('Not found');
            }

        } catch (Exception $e) {
            return response()->error($e->getMessage(), 404);
        }

    }

    public function updateUser(UpdateRequest $request)
    {
        try {
            $validator=$request;
            $user=User::where('email',$request->data['email'])->first();
            if(isset($user))
            {
                $user_id=$user->id;
            }
            else{
                throw new Exception('User do not exists');
            }

            $data=$validator->validated();
            if($request->has('name'))
            {
                $data=array_merge(
                    $data,
                    ['name' =>$request->name]
                );
            }

            if($request->has('email'))
            {
                $data=array_merge(
                    $data,
                    ['email' =>$request->email]
                );
            }

            if($request->has('age'))
            {
                $data=array_merge(
                    $data,
                    ['age' =>$request->age]
                );
            }

            if($request->has('password'))
            {
                $data=array_merge(
                    $data,
                    ['password' =>Hash::make($request->password)]
                );
            }

            if($request->has('profile_picture'))
            {

                $before=$user['profile_picture'];

                if($before!="public/profile/default/user.png")
                {

                    File::delete($before);
                }

                    $image = $request->profile_picture;
                    $pos  = strpos($image, ';');
                    $type = explode(':', substr($image, 0, $pos))[1];
                    $ext=explode('/',$type);
                    $image = str_replace('data:image/'.$ext[1].';base64,', '', $image);
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


            $updated = $user->update($data);
            if($updated)
            {
                $updated_user=User::find($user_id)->first();
                $data['name']=$updated_user->name;
                $data['email']=$updated_user->email;
                $data['password']=$request->password;
                $jwt=(new jwtService)->gettokenencode($data);
                $updated_user->remember_token=$jwt;
                $updated = $updated_user->save();
                if($updated)
                {
                    return response()->success([
                        'message' => 'User successfully Updated',
                        'remember_token'=>$jwt
                    ], 200);
                }
            }

            throw new Exception('Not updated');
        } catch (Exception $e) {
            return response()->error($e->getMessage(),203);
        }
    }

    public function verify($email)
    {
        try {
            if(User::where("email",$email)->value('verify') == 1)
            {
                throw new Exception('You have already verified your account');
            }
            else
            {
                $update=User::where("email",$email)->update(["verify"=>1]);
                if($update){
                    return response()->success(
                        'Your Acount is verified'
                    , 200);
                }else{
                    throw new Exception('Invalid Email. ');
                }
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(),200);
        }

    }

    public function logout(Request $request)
    {
        $user=User::where('email',$request->data['email'])->first();
        $user->remember_token=Null;
        $logout =$user->save();

        if($logout)
        {
            return response()->success([
                'message' => 'User has been logout'
            ], 200);
        }


    }




}
