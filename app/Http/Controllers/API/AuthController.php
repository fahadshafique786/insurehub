<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Response;
use Redirect;
use File;

class AuthController extends BaseController
{

    public function register(Request $request)
    {

        $data = Validator::make($request->all(), [

            'first_name' => 'required',
            'last_name' =>  'required',
            'user_name' => 'required',
            'email'     =>      'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password',

        ]);

        if ($data->fails()) {

            return $this->sendError('Validation Error.', $data->errors());
        }

        try {

            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->user_name = $request->user_name;
            $user->email     = $request->email;
            $user->password  = Hash::make($request->password);
            $user->mobile_no = $request->mobile_no;
            if ($request->file('profile_img')) {
                $file = $request->file('profile_img');
                $extension       = $file->getClientOriginalExtension();
                $image_convert   = time() . '.' . $extension;
                $destinationPath = 'https://developer.inxurehub.com/public/profile_img/';
                $file->move(public_path('profile_img'), $image_convert);
                $user->profile_img = $destinationPath . $image_convert;
            }

            $user->save();

            return $this->sendResponse($user, 'User register successfully.');

        } catch (Exception $e) {

            return $this->sendError('User Not register.');
        }
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required'
        ]);

        if ($validator->fails()) {

            return $this->sendError('Validation Error.', $validator->errors());
        }

        $credentials = $request->only(['email', 'password']);

        if(!Auth::attempt($credentials)){
            return $this->sendError('Invalid Email and Password.');
        }
        $user = Auth::user();
        return $this->sendResponse($user,'User login successfully.');

        //return $this->sendResponse($data,'User login successfully.');
    }


    public function viewProfile(Request $request){

//        dd(1230);
        $validator = Validator::make($request->all(),[
            "id" => 'required'
        ]);
        if ($validator->fails()) {

            return $this->sendError('Validation Error.', $validator->errors());
        }

        $data = User::find($request->id);

        if(!$data == NULL){

            return $this->sendResponse($data,'User Profile');

        }else{

            return $this->sendError('User not found!');

        }

    }

    private function userExits($email)
    {

        return User::where(['email' => $email])->exists();
    }
}
