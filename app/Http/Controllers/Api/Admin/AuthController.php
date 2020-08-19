<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\Admin;
use JWTAuth;

use Response;
class AuthController extends Controller
{

    use GeneralTrait;
    public function login(Request $request)
    {

        try {
            $rules = [
                "email" => "required|exists:admins|email",

                "password" => "required"
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            }

            //login

                   $credentials = $request -> only(['email','password']) ;

           $token =  Auth::guard('admin-api') -> attempt($credentials);

           if(!$token)
               return $this->returnError('E001','بيانات الدخول غير صحيحة');

             $admin = Auth::guard('admin-api') -> user();
             $admin -> api_token = $token;
            //return token
             return $this -> returnData('admin' , $admin);

        }catch (\Exception $ex){
            return $this->returnError($ex->getCode(), $ex->getMessage());
        }

    }
      public function register( Request $request){
        $rules = [
                "email" => "required|email|unique:admins",

                "password" => "required"
            ];

            $validator = Validator::make($request->all(), $rules);

        if ($validator -> fails()) {
            # code...
           $code = $this->returnCodeAccordingToInput($validator);
                return $this->returnValidationError($code, $validator);
            
        }

        Admin::create([
            
            'email' => $request->get('email'),
            'password'=> bcrypt($request->get('password'))
        ]);
        $user = Admin::first();
        $token = JWTAuth::fromUser($user);
        
        return Response::json( compact('token'));
        
        
    }
}
