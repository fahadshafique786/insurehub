<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Exception;
use Illuminate\Support\Facades\Validator;


class QuotationController extends BaseController
{
    protected $OZONE_API_URL;

    public function __construct()
    {
        $this->OZONE_API_URL = config('ozone.OZONE_API_URL');
    }

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

    public function SaveQuotationAdditionalInfo(Request $request){

        try{

            $data = Validator::make($request->formDatas, [
                'customer_quotation_id' => 'required|integer',
                'quotation_risk_id' => 'required|integer',
//                'date_of_birth' => 'required',
//                'first_name' => 'required|string',
//                'last_name' => 'required|string',
//                'cnic_number' => 'required',
//                'mobile_no' => 'required',
//                'city' => 'required|integer',
            ]);

            if ($data->fails()) {
                return $this->sendError('Validation Error.', $data->errors(),422);
            }


            $additionalInformationJson = json_encode($request->formDatas);

            $updateArr = [
                'additional_info_json' => $additionalInformationJson
            ];

            Order::where('customer_quotation_id',$request->formDatas['customer_quotation_id'])->update($updateArr);

//            return $this->sendResponse('',"Quotation Additional Information Data Saved");

            $ozoneResponse = $this->ozoneSaveQuotationAdditionalInfo($request);

            if($ozoneResponse->code == 200) {

                $orderSuccessResponse =  $ozoneResponse->data;
                return $this->sendResponse($orderSuccessResponse,"Quotation Data Updated");
            }
            else{
                return $this->sendError('Quotation Data Update Failed!',$ozoneResponse);
            }


        }catch(Exception $e){

            $exceptionCode = "400";
            if($e->getCode()){
                $exceptionCode = $e->getCode();
            }

            return $this->sendError('Quotation Data Update Failed!',$e->getMessage(),$exceptionCode);

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

        $url = $this->OZONE_API_URL . "customer/quote_fields?" . $fields;
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

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', $this->OZONE_API_URL . 'customer/quotation',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest,$options)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }

    public function ozoneSaveQuotationAdditionalInfo($request){

        $bearerToken = $request->header('Authorization');

        $guzzleClient = new Client([
            'verify' => false
        ]);

        $cnicExpiryDate = (isset($request->formDatas['cnic_expirydate'])) ? $request->formDatas['cnic_expirydate'] : "";

        $vehicleImage = public_path('classes_img').'/'."motor.png";

        /******* static payload prepared for temporary testing purpose **********/
        $options = [

            'multipart' => [
                [
                    'name' => 'customer_quotation_id',
                    'contents' => (isset($request->formDatas['customer_quotation_id'])) ? $request->formDatas['customer_quotation_id'] : ""
                ],
                [
                    'name' => 'quotation_risk_id',
                    'contents' => (isset($request->formDatas['quotation_risk_id'])) ? $request->formDatas['quotation_risk_id'] : ""
                ],
                [
                    'name' => 'first_name[]',
                    'contents' =>  (isset($request->formDatas['first_name'])) ? $request->formDatas['first_name'] : ""
                ],
                [
                    'name' => 'last_name[]',
                    'contents' =>  (isset($request->formDatas['last_name'])) ? $request->formDatas['last_name'] : ""
                ],
                [
                    'name' => 'date_of_birth[]',
                    'contents' => Carbon::parse($request->formDatas['date_of_birth'])->format('d-m-Y')
                ],
                [
                    'name' => 'cnic[]',
                    'contents' =>  (isset($request->formDatas['cnic_number'])) ? $request->formDatas['cnic_number'] : ""
                ],
                [
                    'name' => 'cnic_number[]',
                    'contents' =>  (isset($request->formDatas['cnic_number'])) ? $request->formDatas['cnic_number'] : ""
                ],
                [
                    'name' => 'mobile_no[]',
                    'contents' =>  (isset($request->formDatas['mobile_no'])) ? $request->formDatas['mobile_no'] : ""
                ],
                [
                    'name' => 'city[]',
                    'contents' =>  (isset($request->formDatas['city'])) ? $request->formDatas['city'] : ""
                ],
                [
                    'name' => 'father_name[][]',
                    'contents' => (isset($request->formDatas['father_name'])) ? $request->formDatas['father_name'] : ""
                ],
                [
                    'name' => 'mother_name[][]',
                    'contents' => (isset($request->formDatas['mother_name'])) ? $request->formDatas['mother_name'] : ""
                ],
                [
                    'name' => 'gender[][]',
                    'contents' => (isset($request->formDatas['gender'])) ?  $request->formDatas['gender'] : ""
                ],
                [
                    'name' => 'nationality[][]',
                    'contents' => (isset($request->formDatas['nationality'])) ? $request->formDatas['nationality'] : ""
                ],
                [
                    'name' => 'cnic_issuancedate[][]',
                    'contents' => (isset($request->formDatas['cnic_issuancedate'])) ? $request->formDatas['cnic_issuancedate'] : ""
                ],
                [
                    'name' => 'cnic_expirydate[][]',
                    'contents' => Carbon::parse($cnicExpiryDate)->format('d-m-Y')
                ],
                [
                    'name' => 'resident_status[][]',
                    'contents' => (isset($request->formDatas['resident_status'])) ? $request->formDatas['resident_status'] : ""
                ],
                [
                    'name' => 'profession[][]',
                    'contents' => (isset($request->formDatas['profession'])) ? $request->formDatas['profession'] : ""
                ],
                [
                    'name' => 'foreign_pep[][]',
                    'contents' => (isset($request->formDatas['foreign_pep'])) ? $request->formDatas['foreign_pep'] : ""
                ],
                [
                    'name' => 'domestic_pep[][]',
                    'contents' => (isset($request->formDatas['domestic_pep'])) ? $request->formDatas['domestic_pep'] : ""
                ],
                [
                    'name' => 'international_pep[][]',
                    'contents' => (isset($request->formDatas['international_pep'])) ? $request->formDatas['international_pep'] : ""
                ],
                [
                    'name' => 'pep_relative[][]',
                    'contents' => (isset($request->formDatas['pep_relative'])) ? $request->formDatas['pep_relative'] : ""
                ],
                [
                    'name' => 'email_id[]',
                    'contents' => (isset($request->formDatas['email_id'])) ? $request->formDatas['email_id'] : ""
                ],
                [
                    'name' => 'address[][]',
                    'contents' => (isset($request->formDatas['address'])) ? $request->formDatas['address'] : ""
                ],
                [
                    'name' => 'registration_book[]',
                    'contents' => (isset($request->formDatas['reg_no'])) ? $request->formDatas['reg_no'] : ""
                ],
                [
                    'name' => 'vehicle_images[0][]',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($vehicleImage, 'r'),
                    'filename' => $vehicleImage,
                    'headers' => [
                        'Content-Type' => '<Content-type header>'
                    ]
                ],
                [
                    'name' => 'vehicle_images[0][]',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($vehicleImage, 'r'),
                    'filename' => $vehicleImage,
                    'headers' => [
                        'Content-Type' => '<Content-type header>'
                    ]
                ],
                [
                    'name' => 'vehicle_images[0][]',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($vehicleImage, 'r'),
                    'filename' => $vehicleImage,
                    'headers' => [
                        'Content-Type' => '<Content-type header>'
                    ]
                ],
                [
                    'name' => 'vehicle_images[0][]',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($vehicleImage, 'r'),
                    'filename' => $vehicleImage,
                    'headers' => [
                        'Content-Type' => '<Content-type header>'
                    ]
                ],
                [
                    'name' => 'reg_no[][]',
                    'contents' => (isset($request->formDatas['reg_no'])) ? $request->formDatas['reg_no'] : "1"
                ],
                [
                    'name' => 'engine_no[]',
                    'contents' => (isset($request->formDatas['engine_no'])) ? $request->formDatas['engine_no'] : "1"
                ],
                [
                    'name' => 'place_of_birth[]',
                    'contents' => (isset($request->formDatas['place_of_birth'])) ? $request->formDatas['place_of_birth'] : ""
                ],
                [
                    'name' => 'chassis_no[]',
                    'contents' => (isset($request->formDatas['chassis_no'])) ? $request->formDatas['chassis_no'] : ""
                ],
                [
                    'name' => 'vehicle_color[][]',
                    'contents' => (isset($request->formDatas['vehicle_color'])) ? $request->formDatas['vehicle_color'] : ""
                ],
                [
                    'name' => 'vehicle_color[][]',
                    'contents' => (isset($request->formDatas['vehicle_color'])) ? $request->formDatas['vehicle_color'] : ""
                ],
                [
                    'name' => 'additional_accessories[]',
                    'contents' => (isset($request->formDatas['additional_accessories'])) ? $request->formDatas['additional_accessories'] : ""
                ],
                [
                    'name' => 'parent_id[]',
                    'contents' => '0'
                ]
            ]
        ];


        $headers = [
            'Accept' => 'application/json',
            'distribution' => 'd2c',
            'interface' => 'api',
            'Authorization'=> $bearerToken
        ];

        $ozoneRequest = new \GuzzleHttp\Psr7\Request('POST', $this->OZONE_API_URL.'customer/quotation_additional_information',$headers);
        $res = $guzzleClient->sendAsync($ozoneRequest,$options)->wait();

        $response = json_decode($res->getBody());

        return $response;
    }

}
