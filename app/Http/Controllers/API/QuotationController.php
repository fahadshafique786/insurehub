<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Validator;


class QuotationController extends BaseController
{
    public function GenerateOrder(Request $request){

        try{

            $data = Validator::make($request->formDatas, [
                'sub_class_id' => 'required|integer',
                'plan_id' => 'required',
                'price' => 'required|numeric',
                'new_old' => 'required',
                'manufacturing_year' => 'required',
                'assembly_type' => 'required|integer',
                'tracker_required' => 'required|integer',
                'vehicle_make' => 'required|integer',
                'vehicle_model' => 'required|integer',
                'vehicle_value' => 'required|numeric|min:100000',
            ]);


            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors(),422);
            }

            $ozoneResponse = $this->ozoneGenerateOrder($request);

            if($ozoneResponse->code == 200) {

                $orderSuccessResponse =  $ozoneResponse->data;

                $orderRequest = [
//                    'user_id' => auth()->user()->id,
                    'plan_id'   => $request->formDatas['plan_id'],
                    'subclass_id'   => $request->formDatas['sub_class_id'],
                    'request_json' => json_encode($request->formDatas),
                    'customer_quotation_id' => $orderSuccessResponse->quotation_details[0]->customer_quotation_id,
                    'product_id' => $orderSuccessResponse->quotation_details[0]->product_id,
                    'response_json' => json_encode($orderSuccessResponse),
                    'step_no'   => 2,
                    'status' => $orderSuccessResponse->quotation_details[0]->status,
                    'payment_status' => $orderSuccessResponse->quotation_details[0]->payment_status,
                ];

                Order::create($orderRequest);

                $orderRequest['quotation_risk_id'] = $orderSuccessResponse->risks[0]->id;

                unset($orderRequest['step_no']);
                unset($orderRequest['request_json']);
                unset($orderRequest['response_json']);

                return $this->sendResponse($orderRequest,"Data Saved");
            }
            else{
                return $this->sendError('Data not saved.',$ozoneResponse);
            }


        }catch(Exception $e){

            $exceptionCode = "400";
            if($e->getCode()){
                $exceptionCode = $e->getCode();
            }

            return $this->sendError('Data not saved!',$e->getMessage(),$exceptionCode);

        }
    }

    public function GetQuotationFormFields(Request $request){

        try{

            $data = Validator::make($request->formDatas, [
                'subclass_id' => 'required|integer',
                'customer_quotation_id' => 'required|integer',
                'quotation_risk_id' => 'required|integer',
            ]);


            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors(),422);
            }

            $ozoneResponse = $this->ozoneGetSubClassFormsAttributes($request);

            if($ozoneResponse->code == 200) {

                $orderSuccessResponse =  $ozoneResponse->data;

                return $this->sendResponse($orderSuccessResponse,"Customer Quotation Fields");
            }
            else{
                return $this->sendError('Customer Quotation Form Fields not found!',$ozoneResponse);
            }


        }catch(Exception $e){

            $exceptionCode = "400";
            if($e->getCode()){
                $exceptionCode = $e->getCode();
            }

            return $this->sendError('Customer Quotation Form Fields not found!',$e->getMessage(),$exceptionCode);

        }
    }

    public function ozoneGetSubClassFormsAttributes($request){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $fields = "customer_quotation_id=".$request->formDatas['customer_quotation_id']."&stage=3&subclass_id=".$request->formDatas['subclass_id']."&quotation_risk_id=".$request->formDatas['quotation_risk_id'];

        $url = "https://live.inxurehub.o3zoned.com/api/customer/quote_fields?" . $fields;
        $bearerToken = $request->header('Authorization');

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
            'Authorization'=> $bearerToken
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('GET',$url,$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }


    public function ozoneGenerateOrder($request){

        $bearerToken = $request->header('Authorization');

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $options = [

            'multipart' => [
                [
                    'name' => 'plan_id',
                    'contents' => $request->formDatas['plan_id']
                ],
                [
                    'name' => 'product_price',
                    'contents' => $request->formDatas['price']
                ],
                [
                    'name' => 'subclass_id',
                    'contents' => $request->formDatas['sub_class_id']
                ],
                [
                    'name' => 'new_old[]',
                    'contents' => $request->formDatas['new_old']
                ],
                [
                    'name' => 'manufacturing_year[]',
                    'contents' => $request->formDatas['manufacturing_year']
                ],
                [
                    'name' => 'vehicle_value[]',
                    'contents' => $request->formDatas['vehicle_value']
                ],
                [
                    'name' => 'assembly_type[]',
                    'contents' => $request->formDatas['assembly_type']
                ],
                [
                    'name' => 'tracker_required[]',
                    'contents' => $request->formDatas['tracker_required']
                ],
                [
                    'name' => 'quantity[]',
                    'contents' => '1'
                ],
                [
                    'name' => 'provide_information_through',
                    'contents' => 'single'
                ],
                [
                    'name' => 'risk_count',
                    'contents' => '1'
                ],
                [
                    'name' => 'vehicle_make[]',
                    'contents' => $request->formDatas['vehicle_make']
                ],
                [
                    'name' => 'vehicle_model[]',
                    'contents' => $request->formDatas['vehicle_model']
                ]
            ]
        ];

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
            'Authorization'=> $bearerToken
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://live.inxurehub.o3zoned.com/api/customer/quotation',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest,$options)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }

}
