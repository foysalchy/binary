<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\SiteSetting;
use Session;
use Auth;
use Cart;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Session::get('user_id');
        $listedproducts = Wishlist::where('user_id', $user_id)->get();
        
        $SiteSetting = SiteSetting::where('status', 1)->first();


        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.wishlist.wishlist', compact('listedproducts'));
 
                break;
 
            case(2):
                 
                return view('templete_two.wishlist.wishlist', compact('listedproducts'));
 
                break;
 
            default:
                $msg = 'Something went wrong.';
        }

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user_id = Session::get('user_id');
        $product_id = $request->product_id;
        Wishlist::where('user_id', $user_id)->where('product_id', $product_id)->delete();
        $notification=array(
            'message' => ' Deletd From Withlist!!',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);

    }

    public function addToWishlist(Request $request){
        $user_id = Session::get('user_id');
        $product_id = $request->product_id;
        $product_details = Product::where('id', $product_id)->first();
        
        $exist_product = Wishlist::where('user_id', $user_id)->where('product_id', $product_id)->first();

        if ($user_id) {
            if ($exist_product) {
                $notification=array(
                    'message' => ' This Product Already Added on Withlist!!',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($notification);
            }else{
                $data = new Wishlist();
                $data->user_id = $user_id;
                $data->product_id = $product_id;
                $data->save();

                $notification=array(
                    'message' => ' Product Added on Withlist!!',
                    'alert-type' => 'success'
                );
                return redirect()->back()->with($notification);
            }

        }else{
            return Redirect()->route('customerLogin')->with('login_pls', "Please Login First !! ");
        }

    }

    public function addToWishlistAjax($id)
    {
        $user_id = Session::get('user_id');
        $product_details = Product::where('id', $id)->first();
        
        $exist_product = Wishlist::where('user_id', $user_id)->where('product_id', $id)->first();


        if ($user_id) {
            if ($exist_product) {

                return json_encode(['status' => 'error', 'Message' => 'This Product Already Added on Withlist!!']);

            
            }else{
                $data = new Wishlist();
                $data->user_id = $user_id;
                $data->product_id = $id;
                $data->save();

                $totalItems = Wishlist::where('user_id', $user_id)->count();
                return json_encode(['status' => 'success', 'Message' => 'Product Added on Withlist!!',  'totalItems' => $totalItems]);
           
            }

        }else{

            return json_encode(['status' => 'error', 'Message' => 'Please Login First !!']);
        }

    }


    public function addToCartAjax(Request $request, $id, $qty)
    {
        $qty = $qty ?? 1;
        $product_details = Product::where('id', $id)->first();
        //$product_status = ProductStockStatus::where('product_id', $id)->first();
        if($product_details->stock_status !='out_of_stock'){
            $data = array();
            $data['id'] = $product_details->id;
            $data['name'] = $product_details->name;
    
            if($product_details->offer_price){
                $data['price'] = $product_details->offer_price;
            }else{
               
                if ($product_details->special_price || $product_details->discount_price || $product_details->regular_price) {
                    if ($product_details->discount_price) {
                        $data['price'] = $product_details->discount_price;
                    }
                    if ($product_details->special_price > 0) {
                        $data['price'] = $product_details->special_price;
                    } else {
                        $data['price'] = $product_details->regular_price;
                    }
                } else {


                    return json_encode(['status' => 'error', 'Message' => 'Please Update This Product Price !!']);

                }               
                
            }
    
            $data['qty'] = $qty;
            $data['weight'] = $product_details->id;
            $data['options']['image'] = $product_details->image;
            $cart = Cart::add($data);

            $contents = Cart::content();


            return view('templete_two.homepage.components.cart', compact('contents'));


            return json_encode(['status' => 'success', 'Message' => 'Added Product In Cart!!']);
    
        }
        
    }


}
