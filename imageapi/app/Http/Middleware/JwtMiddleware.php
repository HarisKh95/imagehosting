<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Service\jwtService;

use Exception;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
                // $key = "example_key";
        // JWT::$leeway = 60;
        try {
            $decoded = (new jwtService)->gettokendecode($request->bearerToken());


            $user=User::query();
            $user=$user->where('email',$decoded['email'])->where('remember_token',$request->bearerToken())->first();
            if(isset($user))
            {

            //     if($user->verify==1)
            //     {
                    if (!Hash::check($decoded['password'], $user->password)) {
                        throw new Exception('Not a valid user token');
                    }
            //     }
            //     else
            //     {
            //         throw new Exception('Please verify the mail first');
            //     }
            }
            else
            {
                throw new Exception('Already Logout');
            }

        } catch (Exception $e) {
            if ($e instanceof \Firebase\JWT\SignatureInvalidException){
                return response()->error('Token is Invalid',401);
            }else if ($e instanceof \Firebase\JWT\ExpiredException){
                return response()->error('Token is Expired',401);
            }else{
                return response()->error($e->getMessage(),401);
            }
        }
        $request=$request->merge(array("data" => $decoded));
        return $next($request);
    }
}
