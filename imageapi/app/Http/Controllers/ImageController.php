<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ImageRequest;
use App\Http\Requests\EmailRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Exception;

class ImageController extends Controller
{

    public function upload(ImageRequest $request)
    {
        try {

            $image = $request->image;
            $pos  = strpos($image, ';');
            $type = explode(':', substr($image, 0, $pos))[1];
            $ext=explode('/',$type);
            $image = str_replace('data:image/'.$ext[1].';base64,', '', $image);
            // $image = str_replace('data:image/jpeg;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imagefile = time().rand().'.'.$ext[1];
            $path = public_path().'//storage//images//'.$imagefile;
            $url=url('storage/images/'.$imagefile);
            $allowedfileExtension=['pdf','jpg','png','jpeg'];
            $check = in_array($ext[1],$allowedfileExtension);
            if($check)
            {
            file_put_contents($path,base64_decode($image));

            }
            else{
                throw new Exception('Invalid image format');
            }

                $uimage = new Image();
                $uimage->name=$request->name;
                $uimage->path=$path;
                $uimage->url=$url;
                $uimage->privacy=$request->privacy;
                $uimage->hidden=$request->hidden;
                $uimage->extension=$ext[1];
                $user=User::where('email',$request->data['email'])->first();
                $user->images()->save($uimage);

                return response()->success('Image uploaded successfully',200);

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function imageUpdate(ImageRequest $request,$id)
    {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $uimage=Image::where('id',$id)->where('user_id',$user->id)->first();
            // dd($image);
            if(isset($uimage))
            {
                if($request->has('image'))
                {

                    File::delete($uimage->path);
                    // dd($image->path);
                    $image = $request->image;
                    $pos  = strpos($image, ';');
                    $type = explode(':', substr($image, 0, $pos))[1];
                    $ext=explode('/',$type);
                    $image = str_replace('data:image/'.$ext[1].'jpeg;base64,', '', $image);
                    // $image = str_replace('data:image/jpeg;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imagefile = time().rand().'.'.$ext[1];
                    $path = public_path().'//storage//images//'.$imagefile;
                    $url=url('storage/images/'.$imagefile);

                    $allowedfileExtension=['pdf','jpg','png','jpeg'];
                    $check = in_array($ext[1],$allowedfileExtension);
                    if($check)
                    {
                    file_put_contents($path,base64_decode($image));
                    }
                    else{
                        throw new Exception('Invalid image format');
                    }

                        $data['path']=$path;
                        $data['url']=$url;
                        $data['extension']=$ext[1];


                }

                if($request->has('name'))
                {
                    $data['name']=$request->name;
                }

                if($request->has('privacy'))
                {
                        $data['privacy']=$request->privacy;

                }

                if($request->has('hidden'))
                {
                        $data['hidden']=$request->hidden;
                }

                $uimage->update($data);

                return response()->success('Image updated successfully',200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function imageRemove(Request $request,$id) {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $uimage=Image::where('id',$id)->where('user_id',$user->id)->first();
            if(isset($uimage))
            {
                File::delete($uimage->path);
                $uimage->delete();
                return response()->success('Image Removed successfully',200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function getAllImages(Request $request)
    {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $uimage=Image::where('user_id',$user->id)->get();
            if(!empty($uimage))
            {

                return response()->success(['image' =>ImageResource::collection($uimage)],200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function getAllPublicImages(Request $request)
    {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $uimage=Image::where('user_id',$user->id)->where('privacy',0)->where('hidden',0)->get();
            if(!empty($uimage))
            {

                return response()->success(['image' =>ImageResource::collection($uimage)],200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function getAllPrivateImages(Request $request)
    {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $uimage=Image::where('user_id',$user->id)->where('privacy',1)->where('hidden',0)->get();
            if(!empty($uimage))
            {

                return response()->success(['image' =>ImageResource::collection($uimage)],200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function getAllHiddenImages(Request $request)
    {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $uimage=Image::where('user_id',$user->id)->where('hidden',1)->get();
            if(!empty($uimage))
            {

                return response()->success(['image' =>ImageResource::collection($uimage)],200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function search(Request $request)
    {
        $user=User::where('email',$request->data['email'])->first();

        try {
            $image=Image::where('user_id',$user->id);
            if($request->has('date'))
            {

               $image=$image->where('updated_at','like',date($request->date)."%");
            }

            if($request->has('time'))
            {
                $image=$image->where('updated_at','like',"%".date($request->time));
            }

            if($request->has('name'))
            {

                $image=$image->where('name',$request->name);
            }

            if($request->has('extension'))
            {
                $image=$image->where('extension',$request->extension);
            }

            if($request->has('privacy'))
            {
                $image=$image->where('privacy',$request->privacy);
            }

            if($request->has('hidden'))
            {
                $image=$image->where('hidden',$request->hidden);
            }

            $image=$image->get();
            if(!empty($image))
            {

                return response()->success(['image' =>ImageResource::collection($image)],200);
            }
            else{
                throw new Exception('Image Not Found');
            }

        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }
    }

    public function sharewith(EmailRequest $request,$id)
    {
        try {
            $user=User::where('email',$request->data['email'])->first();
            $image=Image::where('id',$id)->where('user_id',$user->id)->first();
            if(isset($image))
            {
                $mails=explode(',',$image->shared_mail);
                $check = in_array($request->email,$mails);
                if($check)
                {
                    throw new Exception('Already shared');
                }
                else {
                    $data['shared_mail']=$image->shared_mail.$request->email.',';
                    $updated=$image->update($data);
                    if($updated)
                    {
                        $image_mail_data=[
                            'from'=>$user->name,
                            'image_link'=>url('api/image/shared/'.$id)
                        ];
                        dispatch(new \App\Jobs\image_share($request->email,$image_mail_data));
                        return response()->success('Image shared',200);
                    }
                }
            }
            else {
                throw new Exception('Image Not Found');
            }
        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }

    }

    public function sharedImage(Request $request,$id)
    {

        try {
            $user=User::where('email',$request->data['email'])->first();
            $image=Image::where('id',$id)->first();
            if(isset($image))
            {
                $mails=explode(',',$image->shared_mail);
                $check = in_array($user->email,$mails);
                if($check)
                {
                    if(($image->privacy==1)&&($image->hidden==0))
                    {
                        return response()->success(['image'=>$image->url],200);
                    }
                    elseif (($image->privacy==0)&&($image->hidden==0)) {
                        return response()->success(['image'=>$image->url],200);
                    }
                    else {
                        throw new Exception('Private or hidden');
                    }

                }
                else {
                    throw new Exception('You do  not have access to Image');
                }
            }
            else {
                throw new Exception('Image Not Found');
            }
        } catch (Exception $e) {
            return response()->error($e->getMessage(),404);
        }
    }

}
