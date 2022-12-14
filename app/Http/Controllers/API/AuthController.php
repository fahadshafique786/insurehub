<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use GuzzleHttp\Client;
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

            'first_name'    => 'required|string',
            'middle_name'   => 'nullable|string',
            'last_name'     => 'required|string',
            'user_name'     => 'required|unique:users,user_name',
            'cnic'          => 'required|numeric|digits:13|unique:users,cnic',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required',
            'profile_img'      => 'required',
            'mobile_no'      => 'required',
        ]);

        if ($data->fails()) {

            return $this->sendError('Validation Error.', $data->errors(),422);
        }

        try {

            $user = new User();

            $user->first_name = $request->first_name;
            $user->middle_name = $request->middle_name;
            $user->last_name = $request->last_name;
            $user->user_name = $request->user_name;
            $user->email     = $request->email;
            $user->password  = Hash::make($request->password);
            $user->mobile_no = $request->mobile_no;
            $user->cnic = $request->cnic;
            $user->company_id = 1;
            $user->country_code = "+92";

            if ($request->file('profile_img')) {
                $file = $request->file('profile_img');
                $extension       = $file->getClientOriginalExtension();
                $image_convert   = time() . '.' . $extension;
                $destinationPath = url('/profile_img/');

                $file->move(public_path('profile_img'), $image_convert);
                $user->profile_img = $destinationPath . $image_convert;
                $imagePath = public_path('profile_img/').$image_convert;
                $user->avatar = $imagePath;
            }

            $response = $this->ozoneCustomerRegistration($request,$user);

            unset($user->avatar);

            if($response->code == 200){

                $user->save();

                $user->accessToken = $response->data->token;
            }
            else{

                return $this->sendError($response->code ,'User Not registered.');
            }

            return $this->sendResponse($user, 'User register successfully.');

        } catch (Exception $e) {

            return $this->sendError('User Not registered',$e->getMessage(),'404');

        }
    }

    public function login(Request $request)
    {
        try {


            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string|max:100',
                'password' => 'required'
            ]);

            if ($validator->fails()) {

                return $this->sendError('Validation Error.', $validator->errors());
            }

            $credentials = $request->only(['user_name', 'password']);

            $response = $this->ozoneCustomerLogin($request);

            if($response->code == 200){

                if(!Auth::attempt($credentials)){
                    return $this->sendError('Invalid Username or Password.');
                }

                $user = Auth::user();

                $user->accessToken = $response->data->token;

                return $this->sendResponse($user,'User login successfully.');

            }
            else{
                return $this->sendError($response->code ,'Invalid Credentials.');
            }

        } catch (Exception $e) {

            return $this->sendError('Invalid Credentials',$e->getMessage(),'404');

        }

    }


    public function viewProfile(Request $request){

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

    public function ozoneCustomerRegistration($request,$user){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $customerData = [
            'multipart' => [
                [
                    'name' => 'first_name',
                    'contents' => $request->first_name
                ],
                [
                    'name' => 'last_name',
                    'contents' => $request->last_name
                ],
                [
                    'name' => 'mobile_no',
                    'contents' => $request->mobile_no
                ],
                [
                    'name' => 'cnic',
                    'contents' => $request->cnic
                ],
                [
                    'name' => 'password',
                    'contents' => $request->password
                ],
                [
                    'name' => 'company_id',
                    'contents' => '1'
                ],
                [
                    'name' => 'email',
                    'contents' => $request->email
                ],
                [
                    'name' => 'country_code',
                    'contents' => '+92'
                ],
                [
                    'name' => 'avatar',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($user->avatar, 'r'),
                    'filename' => $user->avatar,
                    'headers' => [
                        'Content-Type' => '<Content-type header>'
                    ]
                ],
                [
                    'name' => 'username',
                    'contents' => $request->user_name
                ]
            ]];



        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://live.inxurehub.o3zoned.com/api/customer/register');
        $res = $guzzleClient->sendAsync($ozoneRequest, $customerData)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }

    public function ozoneCustomerLogin($request){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $customerData = [
            'multipart' => [
                [
                    'name' => 'username',
                    'contents' => $request->user_name
                ],
                [
                    'name' => 'password',
                    'contents' => $request->password
                ]
            ]];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://live.inxurehub.o3zoned.com/api/customer/login');

        $res = $guzzleClient->sendAsync($ozoneRequest, $customerData)->wait();

        $response = json_decode($res->getBody());

        return $response;

    }


}
