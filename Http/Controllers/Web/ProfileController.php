<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SiteSetting;
use Session;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function show()
    {

        $SiteSetting = SiteSetting::where('status', 1)->first();

        $user_id = Session::get('user_id');
        $customer = Customer::where('id', $user_id)->first();
        $orders = Order::where('user_id', $user_id)->orderBy('id', 'desc')->get();

        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.profile.profile', compact('customer', 'user_id', 'orders'));
 
                break;
 
            case(2):
                 
                return view('templete_two.customer.profile', compact('customer', 'user_id', 'orders'));
 
                break;
 
            default:
                $msg = 'Something went wrong.';
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // validate to foysal
        $this->validate($request, [
            'first_name' => 'string',
            'last_name' => 'string',
            'phone' => 'string',
            'email' => 'string',
            'city' => 'string',
            'post_code' => 'string',
            'country' => 'string',
            'address' => 'string',
        ]);

        $user_id = $request->user_id;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $gender = $request->gender;
        $phone = $request->phone;
        $email = $request->email;
        $city = $request->city;
        $post_code = $request->post_code;
        $country = $request->country;
        $address = $request->address;

        $customer = Customer::where('id', $user_id)->first();
        $customer->first_name = $first_name;
        $customer->last_name = $last_name;
        $customer->phone = $phone;
        $customer->gender = $gender;
        $customer->email = $email;
        $customer->city = $city;
        $customer->post_code = $post_code;
        $customer->country = $country;
        $customer->address = $address;
        $customer->status = 1;
        $customer->save();

        Session::put('user_id', $customer->id);
        Session::put('user_type', 'customer');
        Session::put('name', $customer->first_name);
        session()->flash('info_update', "Information Updated Successful !! ");

        return view('frontend.profile.profile', compact('customer', 'user_id'));



    }

    public function updatePassword(Request $request)
    {
        $this->validate($request, [
        'old_pass' => 'required',
        'password' => 'min:6|required_with:password_confirmation|same:password_confirmation',
        ]);

        $user_id = $request->user_id;
        $old_pass = $request->old_pass;
        $password = $request->password;
        $exit_customer = Customer::where('id', $user_id)->first();

        if ($exit_customer) {
            if ($exit_customer->password == md5($old_pass)) {
                Customer::where(["id" => $exit_customer->id])->update(["password" => md5($password)]);
                session()->flash('pass_update', "Password Update Successfull !! ");
                return redirect()->back();
            }else{
                session()->flash('pass_not_match', "Password Doesn't Match !! ");
                return redirect()->back();
            }
        }
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

    public function viewOrderDetails(Request $request)
    {
        $order = Order::where('id', $request->id)->first();
        $order_details = OrderDetail::where('order_id', $request->id)->get();

        return view('frontend.profile.order.OrderDetails', compact('order_details', 'order'));
    }


}
