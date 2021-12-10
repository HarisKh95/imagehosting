<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ImageRequest;
use App\Models\Image;
use App\Models\User;
use Exception;

class ImageController extends Controller
{

    public function upload(ImageRequest $request)
    {
        try {
            if ($request->hasFile('image'))
            {
                //get image file
                $image = $request->file('image');

                $name=$image->getClientOriginalName();
                $ext=$image->getClientOriginalExtension();
                //generate a new name of image
                $imagefile = time().rand().'.'.$ext;
                $destinationPath = public_path('/images/');
                // $image->storeAs('public/images',$imagefile);
                $imgUrl = url('/storage/' .$image->storeAs('images',$imagefile));
                $uimage = new Image();
                $uimage->name=$name;
                $uimage->path=$destinationPath.$imagefile;
                $uimage->url=$imgUrl;
                $uimage->extension=$ext;
                $user=User::where('email',$request->data['email'])->first();
                $user->images()->save($uimage);

                return response()->success('Image uploaded successfully',200);

            }


        } catch (Exception $e) {

            return response()->error($e->getMessage(),404);
        }



    }
}
