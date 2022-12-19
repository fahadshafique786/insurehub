<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Validator;


class ProductController extends BaseController
{
    public function getProductsList(Request $request){

        try{

            $data = Validator::make($request->all(), [
                'sub_class_id' => 'required|integer',
                'new_old' => 'required',
                'manufacturing_year' => 'required',
                'assembly_type' => 'required|integer',
                'tracker_required' => 'required|integer',
                'vehicle_make' => 'required|integer',
                'vehicle_model' => 'required|integer',
//                'vehicle_value' => 'required',
            ]);

            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors());
            }


            $ozoneResponse = $this->ozoneGetCustomerQuotation($request);


            if($ozoneResponse->code == 200) {
                $customFields =  $ozoneResponse->data->products;
                return $this->sendResponse($customFields,"Product Plans List");
            }
            else{
                return $this->sendError('Plans not found.');
            }


        }catch(Exception $e){

            dd($e->getMessage());

            return $this->sendError('Plans not found.',$e->getMessage());
        }
    }

    public function ozoneGetCustomerQuotation($request){

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $options = [
            'multipart' => [
                [
                    'name' => 'subclass_id',
                    'contents' => $request->sub_class_id
                ],
                [
                    'name' => 'new_old[]',
                    'contents' => $request->new_old
                ],
                [
                    'name' => 'manufacturing_year[]',
                    'contents' => $request->manufacturing_year
                ],
                [
                    'name' => 'vehicle_value[]',
                    'contents' => $request->vehicle_value
                ],
                [
                    'name' => 'assembly_type[]',
                    'contents' => $request->assembly_type
                ],
                [
                    'name' => 'tracker_required[]',
                    'contents' => $request->tracker_required
                ],
                [
                    'name' => 'quantity[]',
                    'contents' => '1'
//                    'contents' => $request->quantity
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
                    'contents' => $request->vehicle_make
                ],
                [
                    'name' => 'vehicle_model[]',
                    'contents' => $request->vehicle_model
                ]
            ]
        ];

        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', 'https://live.inxurehub.o3zoned.com/api/customer_quotation',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest,$options)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }

}
