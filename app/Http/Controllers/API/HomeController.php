<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;
use App\Models\Banners;
use App\Models\PromotionBanner;
use App\Models\Classes;

class HomeController extends BaseController
{
    public function index(){
        
            $classes = Classes::get();
            $banner = Banners::get();
            $promotionBanner = PromotionBanner::orderBy('id','DESC')->limit(3)->get();
            
            $data = array();
            
            $data['banner'] = $banner;
            $data['promotionBanner'] = $promotionBanner;
            $data['classes'] = $classes;
            
            return $this->sendResponse($data,'Success');
       
    }
}
