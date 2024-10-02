<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SiteSetting;
use Auth;
use Session;
use Mail;
use App\Models\Customer;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_id = Auth::user();
        if($user_id){
            $orders = Order::orderBy('id','desc')->get();
            return view('backend.admin.order.index', compact('orders'));
        }else{
            return redirect('login');
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
        
        $user_id = Auth::user();
        if($user_id){
            $order = Order::findorfail($id);
            $orderDetails = OrderDetail::where('order_id', $id)->get();
            $toal_p_price = OrderDetail::where('order_id', $id)->sum('qty_total_amount');
    
            $var_total_cost = $order->total_cost;
            $total_cost = (float) str_replace(',', '', $var_total_cost);
            $discount = $order->discount;
            $total = $total_cost-$discount;
    
            $SiteSetting = SiteSetting::first();
    
            return view('backend.admin.order.details', compact('order', 'orderDetails', 'toal_p_price', 'total', 'SiteSetting'));
        }else{
            return redirect('login');
        }
        
        

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $user_id = Auth::user();
        if($user_id){
            $order = Order::findorfail($id);
            $orderDetails = OrderDetail::where('order_id', $id)->get();
            $toal_p_price = OrderDetail::where('order_id', $id)->sum('qty_total_amount');
    
            $var_total_cost = $order->total_cost;
            $total_cost = (float) str_replace(',', '', $var_total_cost);
            $discount = $order->discount;
            $total = $total_cost-$discount;
    
            $SiteSetting = SiteSetting::first();
            return view('backend.admin.order.edit', compact('order', 'orderDetails', 'toal_p_price', 'total', 'SiteSetting'));
        }else{
            return redirect('login');
        }
        
        

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
        $data = Order::find($id);
        $data->status = $request->status;
        $data->save();
        $notification=array(
            'message' => 'Order Status Updated Successfully Done',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function update_shipping_details(Request $request, $id)
    {
        $validated = $request->validate([
            'shipping_first_name' => 'string',
            'shipping_last_name' => 'string', 
            'shipping_email' => 'string',
            'address' => 'string',
            'city' => 'string',
            'postcode' => 'string',
            'country' => 'string',
        ]);
        // validate to foysal
        $data = Order::find($id);
        $data->shipping_first_name = $request->shipping_first_name;
        $data->shipping_last_name = $request->shipping_last_name;
        $data->shipping_phone = $request->shipping_phone;
        $data->shipping_email = $request->shipping_email;
        
        $data->address = $request->address;
        $data->city = $request->city;
        $data->postcode = $request->postcode;
        $data->country = $request->country;
        $data->save();
        $notification=array(
            'message' => 'Customer Shipping Details Updated Successfully Done',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function invoicePrint($id){
        
        $user_id = Auth::user();
        if($user_id){
            $order = Order::findorfail($id);
            $orderDetails = OrderDetail::where('order_id', $id)->get();
    
            $toal_p_price = OrderDetail::where('order_id', $id)->sum('qty_total_amount');
    
            $var_total_cost = $order->total_cost;
            $total_cost = (float) str_replace(',', '', $var_total_cost);
            $discount = $order->discount;
            $total = $total_cost-$discount;
    
            $SiteSetting = SiteSetting::first();
    
            return view('backend.admin.order.invoice_print', compact('order', 'orderDetails', 'toal_p_price', 'total', 'SiteSetting'));
        }else{
            return redirect('login');
        }
        
        

    }
    
       public function send_email(){
        dd('send_email');
        // $customer = Customer::where('id',$id)->first();
        // return view('backend.admin.order.mail_details',compact('customer'));
    }
    public function save_email(Request $request){
        $validated = $request->validate([
            'subject' => 'string',
            'description' => 'string',
        ]);
        // validate to foysal
        $customer = Customer::find($request->customer_id);
        $name = $customer->first_name.' '.$customer->last_name;
        $email = $customer->email;
        $subject = $request->subject;
        $description = $request->description;
        $data =new OrderMail();
        $data->customer_id = $request->customer_id;
        $data->subject = $request->subject;
        $data->description = $request->description;
        $data->save();
        Session::put('subject', $request->subject);
        $message_date = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'description' => $description,
        ];
        Mail::send('backend.admin.email.in_mail_template', $message_date, function ($message)use($email) {
            $message->to($email);
            $message->subject(Session::get('subject'));
        });
        $notification=array(
            'message' => 'Email Send.Please Check Your Email',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }


}
