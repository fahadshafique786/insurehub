<?php

namespace App\Http\Controllers\API;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    public  function getAllPaymentMethods(){

        $paymentMethods = PaymentMethod::all();

        return $this->sendResponse($paymentMethods,"Quotation Additional Information Data Saved");

    }
}
