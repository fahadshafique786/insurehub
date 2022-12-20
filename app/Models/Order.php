<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_quotation_id',
        'request_json',
        'response_json',
        'additional_info_json',
        'payment_method_info_json',
        'plan_id',
        'subclass_id',
        'product_id',
        'step_no',
        'status',
        'payment_status',
        'quotation_requested_at',
        'quotation_approved_at'
    ];
}
