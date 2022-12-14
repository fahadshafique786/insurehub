<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
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

            $classImageUrl = url('classes_img') . '/';

            $classes = Classes::select(DB::raw("id, name , CONCAT('$classImageUrl',logo) AS logo"))->get();

            return $this->sendResponse($classes,"Classes List");

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
