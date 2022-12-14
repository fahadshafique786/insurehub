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


        }catch(Exception $e){


            return $this->sendError('Classes not found.',$e->getMessage());
        }
    }

    public function getSubClasses(Request $request){

        try{

            $data = Validator::make($request->all(), [
                'class_id' => 'required|integer',
            ]);

            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors());
            }

            $ozoneResponse = $this->ozoneGetSubClassesList();

            if($ozoneResponse->code == 200) {

                $subClassesObject =  $ozoneResponse->data->sub_classes;

//                dd($subClassesObject);

                $subClassesListByMainClass = [];
                foreach($subClassesObject as $obj){
//                    dd($obj ,$obj->class_id == $request->class_id , $obj->class_id,  $request->class_id);
                    if($obj->class_id == $request->class_id){
                        $subClassesListByMainClass[] = $obj;
                    }
                }

                return $this->sendResponse($subClassesListByMainClass,"List of Sub Classes By Main Class");
            }
            else{
                return $this->sendError('Classes not found.',$e->getMessage());
            }


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
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('GET', 'https://live.inxurehub.o3zoned.com/api/get_mainclasses',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest)->wait();

        $response = json_decode($res->getBody());

        return $response;

    }

    public function ozoneGetSubClassesList(){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('GET', 'https://live.inxurehub.o3zoned.com/api/get_subclasses',$headers);
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
