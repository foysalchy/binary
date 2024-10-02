<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class DesktopOfferController extends Controller
{
    public function index(){
          $first_two = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->take(2)->get(); 
          $take_one = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->skip(2)->take(1)->first(); 
          $take_two = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->skip(3)->take(2)->get(); 
          $description = DB::table('offer_page')->where('status', 1)->select('id','description')->orderBy('id','asc')->first(); 
          // dd($description);
          $take_one_one = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->skip(5)->take(1)->first();
          $take_two_two = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->skip(6)->take(2)->get();
          $take_last_two = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->skip(8)->take(2)->get();
          $take_last_three = DB::table('offer_page')->where('status', 1)->orderBy('id','asc')->skip(10)->take(3)->get();
        return view('templete_two.offer.desktop-offer',compact('first_two','take_one','take_two','description','take_one_one','take_two_two','take_last_two','take_last_three'));
    }
    public function offer_details($pagetype){
        //dd($pagetype);
        $offer_details = DB::table('offer_page')->where('offer_type',$pagetype)->first();
        //dd($offer_details);
        $products = DB::table('products')->where('desktop_offer',$pagetype)->paginate(12);
       // dd($products);
        return view('templete_two.offer.offer-details',compact('products','pagetype','offer_details'));
    }
}
