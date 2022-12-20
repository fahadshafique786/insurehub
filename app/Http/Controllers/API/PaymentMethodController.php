<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    public  function getAllPaymentMethods(){

        $paymentMethods = PaymentMethod::all();

        return $this->sendResponse($paymentMethods,"Payment Method List");

    }

    public function StorePaymentInformation(Request $request){

        $paymentMethodInformation = json_encode($request->formDatas);

        $updateArr = [
            'payment_method_info_json' => $paymentMethodInformation
        ];

        Order::where('customer_quotation_id',$request->formDatas['customer_quotation_id'])->update($updateArr);

        return $this->sendResponse('',"Provided Payment Information Stored!");

    }

}
