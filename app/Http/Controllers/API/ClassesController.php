<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\Classes;
use Exception;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\SubClassess;
use Validator;
use Illuminate\Support\Facades\DB as DB;

class ClassesController extends BaseController
{
    public function getClasses(){

        try{


            $ozoneResponse = $this->ozoneGetMainClassesList();

            if($ozoneResponse->code == 200) {
                $classes =  $ozoneResponse->data->main_classes;
                return $this->sendResponse($classes,"Classes List");
            }
            else{
                return $this->sendError('Classes not found.',$e->getMessage());
            }


            //            $classImageUrl = url('classes_img') . '/';
//
//            $classes = Classes::select(DB::raw("id, name , CONCAT('$classImageUrl',logo) AS logo"))->get();


        }catch(Exception $e){


            return $this->sendError('Classes not found.',$e->getMessage());
        }
    }

    public function subClasses(Request $request){

        $data = Validator::make($request->all(), [

            'class_id' => 'required',

        ]);

        if ($data->fails()) {

            return $this->sendError('Validation Error.', $data->errors());
        }

        $subClassess = SubClassess::where('class_id',$request->class_id)->get();

        if(filled($subClassess)){

            return $this->sendResponse($subClassess,"Sub Classes");

        }else{

            return $this->sendError('Sub Classes not found.');

        }
    }

    public function ozoneGetMainClassesList(){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
//            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3N0YWdpbmcuaW54dXJlaHViLm8zem9uZWQuY29tL2FwaS9jdXN0b21lci9sb2dpbiIsImlhdCI6MTY2MzA2NTQxMCwiZXhwIjo1NTY2MzA2NTQxMCwibmJmIjoxNjYzMDY1NDEwLCJqdGkiOiJsWFRQT2M5cVVSYXdvNWxhIiwic3ViIjozODUsInBydiI6IjYyNDU3NTM5YTc3YjY3MDUyOWZiZDY3NTNmMGIzMTE0NGE5YTY3M2UifQ.L55zMiibVbmB57v2ni03VS59P7BiV0g_PZALYBqg414'
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('GET', 'https://live.inxurehub.o3zoned.com/api/get_mainclasses',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest)->wait();

        $response = json_decode($res->getBody());

        return $response;

    }



    public function getVehicle(Request $request){

        $vehicleDetails = Vehicle::get();

        if(filled($vehicleDetails)){

            return $this->sendResponse($vehicleDetails,'Vehicle');
        }else{

            return $this->sendError('Vehicle not found');
        }
    }

    public function getVehicleByID(Request $request){

        $data = Validator::make($request->all(), [

            'vehicle_id' => 'required',

        ]);

        if ($data->fails()) {

            return $this->sendError('Validation Error.', $data->errors());
        }

        $vehicleDetails = VehicleModel::where('vechicle_id',$request->vehicle_id)->get();

        if(filled($vehicleDetails)){

            return $this->sendResponse($vehicleDetails,'Vehicle');
        }else{

            return $this->sendError('Not found');
        }

    }




}
