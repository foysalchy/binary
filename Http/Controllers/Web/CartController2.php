<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStockStatus;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderDetail;
use Cart;
use Session;
use Mail;


class CartController extends Controller
{
    public function add_to_cart(Request $request)
    {
        $product_id = $request->product_id;
        $qty = $request->qty;
        $product_details = Product::where('id', $product_id)->first();
        //$product_status = ProductStockStatus::where('product_id', $product_id)->first();
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
                    $notification = array(
                        'message' => 'Please Update This Product Price !!',
                        'alert-type' => 'warning'
                    );
                    return redirect()->back()->with($notification);
                }               
                
            }
    

    
    
            $data['qty'] = $qty;
            $data['weight'] = $product_details->id;
            $data['options']['image'] = $product_details->image;
            $cart = Cart::add($data);
    
            $notification = array(
                'message' => ' Added Product In Cart!!',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
        
    }

    public function cart_page(Request $request)
    {
        $contents = Cart::content();
        $product_id = $request->product_id;
        $qty = $request->qty;
        $product_details = Product::where('id', $product_id)->first();

        // foreach ($contents as $content) {
        //     if ($content->id == $product_id) {
        //         $notification = array(
        //             'message' => ' Already Added In Cart!!',
        //             'alert-type' => 'error'
        //         );
        //         return redirect()->back()->with($notification);
        //     }
        // }

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
                    $notification = array(
                        'message' => 'Please Update This Product Price !!',
                        'alert-type' => 'warning'
                    );
                    return redirect()->back()->with($notification);
                }    
                
            }



        $data['qty'] = $qty;
        $data['weight'] = $product_details->id;
        $data['options']['image'] = $product_details->image;

        $cart = Cart::add($data);



        $contents = Cart::content();
        $sub_total = Cart::subtotal();
        $total = Cart::subtotal();

        $sub_total_num = (float) str_replace(',', '', $sub_total);

        return view('frontend.shop.cart', compact('cart', 'contents', 'sub_total',  'total'));
    }



    public function add_cart($id)
    {
        //sleep(3);
        $product_details = Product::where('id', $id)->first();
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
                $notification = array(
                    'message' => 'Please Update This Product Price !!',
                    'alert-type' => 'warning'
                );
                return redirect()->back()->with($notification);
            }
        }

        $data['qty'] = 1;
        $data['weight'] = $product_details->id;
        $cart = Cart::add($data);
        return json_encode([
            'status' => 'success',
            'Message' => 'Successfully Added On Your Cart',
            'title' => $product_details->name,
            'slug' => $product_details->slug,
            'image' => $product_details->image,
            'totalItems' => Cart::content()->count(),
            'totalPrice' => Cart::subtotal()
        ]);
    }

    public function cart()
    {
        $contents = Cart::content();
        $sub_total = Cart::subtotal();
        $total = Cart::subtotal();

        return view('frontend.shop.cart', compact('contents', 'sub_total', 'total'));
    }

    public function remove_cart($rowId)
    {
        Cart::remove($rowId);

        $contents = Cart::content();
        $sub_total = Cart::subtotal();
        $total = Cart::subtotal();
        $notification = array(
            'message' => 'Deleted Product From Cart !!',
            'alert-type' => 'error'
        );
        return view('frontend.shop.cart', compact('contents', 'sub_total', 'total'))->with($notification);
    }

    public function clearCart()
    {
        $contents = Cart::content();
        foreach($contents as $key => $value){
            Cart::remove($key);
        }
        return redirect('/cart');
    }

    public function update_cart(Request $request)
    {
        $rowid = $request->rowid;
        $quantity = $request->quantity;
        if (!empty($rowid)) {
            foreach ($quantity as $key => $value) {
                $cart = Cart::get($rowid[$key]);
                Cart::update($rowid[$key], $value);
            }
        }
        $contents = Cart::content();
        $sub_total = Cart::subtotal();
        $total = Cart::subtotal();
        $notification = array(
            'message' => 'Cart Updated!!',
            'alert-type' => 'success'
        );
        
        return redirect()->back()->with($notification);
        //return view('frontend.shop.cart', compact('contents', 'sub_total', 'total'))->with($notification);
    }

    public function apply_coupon(Request $request)
    {

        $contents = Cart::content();
        $couponCode = $request->couponCode;
        $db_code = Coupon::where('code', $couponCode)->first();

        if ($couponCode && $db_code) {
            $sub_total = Cart::subtotal();
            $int_sub_total = (float) str_replace(',', '', $sub_total);
            $CoupondiscountPrice = ($int_sub_total * $db_code->discount) / 100;
            $total = $int_sub_total - $CoupondiscountPrice;
            Session::put('discount', $CoupondiscountPrice);
            return view('frontend.shop.cart', compact('contents', 'sub_total', 'CoupondiscountPrice', 'couponCode', 'total'));
        } else {
            $sub_total = Cart::subtotal();
            $total = Cart::subtotal();
            session()->flash('error', "Coupon Code Doesn't Match !! ");
            return view('frontend.shop.cart', compact('contents', 'sub_total', 'total'));
        }
    }


    public function checkout(Request $request)
    {
        $user_id = Session::get('user_id');

        $contents = Cart::content();
        $sub_total = Cart::subtotal();
        $sub_total_num = (float) str_replace(',', '', $sub_total);

        $total = $request->total;
        $CoupondiscountPrice = $request->CoupondiscountPrice;


        if ($user_id) {
            if ($sub_total == 0) {
                session()->flash('empty_notif', "Your cart is empty !!");
                return view('frontend.shop.cart', compact('contents', 'sub_total', 'total', 'sub_total_num', 'CoupondiscountPrice'));
            } else {
                return view('frontend.shop.checkout', compact('contents', 'sub_total', 'sub_total_num', 'total', 'CoupondiscountPrice'));
            }
        } else {
            session()->flash('login_notify', "Please Login Or Registration For Continue Your Shopping !!");
            return view('frontend.customer.customerLogin');
        }
    }

    public function submitOrder(Request $request)
    {
        $this->validate($request, [
            'shipping_first_name' => 'required',
            'shipping_last_name' => 'required',
            'shipping_phone' => 'required',
            'shipping_email' => 'required',
            'city' => 'required ',
            'postcode' => 'required ',
            'country' => 'required ',
            'address' => 'required ',
        ]);

        $shipping_first_name = $request->shipping_first_name;
        $shipping_last_name = $request->shipping_last_name;
        $shipping_phone = $request->shipping_phone;
        $shipping_email = $request->shipping_email;
        $city = $request->city;
        $postcode = $request->postcode;
        $country = $request->country;
        $address = $request->address;
        $message = $request->message;
        
        $total = Cart::subtotal();

        $discount = $request->CoupondiscountPrice;

        if ($discount) {
            $discount_total = $request->total;
        }

        $invoice_id =  rand(10000, 100000);
        $user_id = Session::get('user_id');
        $total_qty = Cart::count();


        $order = new Order();
        $order->user_id = $user_id;
        $order->invoice_id = $invoice_id;
        $order->total_qty = $total_qty;
        $order->total_cost = $total;
        if ($discount) {
            $order->discount_total = $discount_total ?? '0';
        }
        $order->discount = $discount ?? '0';
        $order->shipping_first_name = $shipping_first_name;
        $order->shipping_last_name = $shipping_last_name;
        $order->shipping_phone = $shipping_phone;
        $order->shipping_email = $shipping_email;
        $order->city = $city;
        $order->postcode = $postcode;
        $order->country = $country;
        $order->address = $address;
        $order->message = $message;
        $order->status = 0;
        $order->save();

        $order_id = $order->id;

        $contents = Cart::content();
        foreach ($contents as $content) {
            $orderDetails = new OrderDetail();
            $orderDetails->order_id = $order_id;
            $orderDetails->product_id = $content->id;

            $product_owner = Product::where('id', $content->id)->first();
            $orderDetails->product_price = $content->price;
            $orderDetails->qty_total_amount = $content->price * $content->qty;
            $orderDetails->qty = $content->qty;
            $orderDetails->save();
        }
        $message_date = [
            'order_id' => $order_id,
        ];
        $emails = [$shipping_email, 'itsraihanarifin@gmail.com','monsur@binarylogic.com.bd','aminul0515001@gmail.com'];

        Mail::send('backend.admin.order.success_email',$message_date, function($message) use ($emails)
        {    
            $message->to($emails)->subject('Order Successfully placed.'); 
        });

        Cart::destroy();
        $total = 0;


        return view('frontend.shop.thankyou');
    }
}
