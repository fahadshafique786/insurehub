<?php

namespace App\Http\Controllers\API;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Exception;
use App\Models\Vehicle;
use App\Models\VehicleModel;
use App\Models\SubClassess;
use Illuminate\Support\Facades\Validator;

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
                return $this->sendError('Classes not found.');
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

                $subClassesListByMainClass = [];

                foreach($subClassesObject as $obj){
                    if($obj->class_id == $request->class_id){
                        $subClassesListByMainClass[] = $obj;
                    }
                }

                return $this->sendResponse($subClassesListByMainClass,"List of Sub Classes By Main Class");
            }
            else{
                return $this->sendError('Classes not found.');
            }


        }catch(Exception $e){


            return $this->sendError('Classes not found.',$e->getMessage());
        }

    }

    public function getSubClassFormAttributes(Request $request){

        try{

            $data = Validator::make($request->all(), [
                'sub_class_id' => 'required|integer',
            ]);

            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors());
            }

            $ozoneResponse = $this->ozoneGetSubClassFormsAttributes($request->sub_class_id);

            if($ozoneResponse->code == 200) {
                $customFields =  $ozoneResponse->data->risk_sections->Quote_Information->custom_fields;
                return $this->sendResponse($customFields,"Classes List");
            }
            else{
                return $this->sendError('Classes not found.');
            }


        }catch(Exception $e){

            return $this->sendError('Classes not found.',$e->getMessage());
        }
    }

    public function getChildFormFieldValues(Request $request){

        try{

            $data = Validator::make($request->all(), [
                'child_form_field_id' => 'required|integer',
                'parent_value_id' => 'required|integer',
            ]);

            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors());
            }


            $ozoneResponse = $this->ozoneGetChildFormFieldValues($request);

            if($ozoneResponse->code == 200) {

                $data =  $ozoneResponse->data;
                return $this->sendResponse($data,"Form Field Values");
            }
            else{
                return $this->sendError('Form Field Values Not Found.');
            }


        }catch(Exception $e){


            return $this->sendError('Form Field Values Not Found.',$e->getMessage());
        }
    }


    /************* OZONE API INTEGRATION ************/


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

    public function ozoneGetSubClassFormsAttributes($subClassId){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $options = [
            'multipart' => [
                [
                    'name' => 'subclass_id',
                    'contents' => $subClassId
                ]
            ]
        ];

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://live.inxurehub.o3zoned.com/api/get_subclass_form',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest,$options)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }

    public function ozoneGetChildFormFieldValues($request){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $options = [
            'multipart' => [
                [
                    'name' => 'child_form_field_id',
                    'contents' => $request->child_form_field_id
                ],
                [
                    'name' => 'parent_value_id',
                    'contents' => $request->parent_value_id
                ]
            ]
        ];

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://live.inxurehub.o3zoned.com/api/get_child_form_field_values',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest,$options)->wait();

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

}
