<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\SiteSetting;
use Session;
use Laravel\Socialite\Facades\Socialite;
use Validator;

class AuthenticationController extends Controller
{
    public function customerLogin()
    {
        $SiteSetting = SiteSetting::where('status', 1)->first();

        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.customer.customerLogin');
 
                break;
 
            case(2):
                 
                return view('templete_two.customer.customerLogin');
 
                break;
 
            default:
                $msg = 'Something went wrong.';
        }



    	
    }
    public function customerRegister()
    {
    	return view('frontend.customer.customerRegister');
    }

    public function customerSubmitRegisterForm(Request $request)
    {
    // validate to foysal
      $validator = Validator::make($request->all(),[
        'first_name' => 'string|required',
        'last_name' => 'string|required',
        'phone' => 'string|required',
        'email' => 'string|required',
        'password' => 'required',
        'password_confirmation' => 'min:6|required_with:password_confirmation|same:password_confirmation',
  
      ]);
      if(!$validator->passes()){

        $notification=array(
            'message' => 'Something went wrong',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
        
    }
    else{

        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $gender = $request->gender;
        $phone = $request->phone;
        $email = $request->email;
        $password = $request->password;


            $customer = new Customer();
            $customer->first_name = $first_name;
            $customer->last_name = $last_name;
            $customer->phone = $phone;
            $customer->gender = $gender ?? 0;
            $customer->email = $email;
            $customer->password = md5($password);
            $customer->str_password = $password;
            $customer->status = 1;
            $customer->save();
            Session::put('user_id', $customer->id);
            Session::put('user_type', 'customer');
            Session::put('name', $customer->first_name);


            
            $notification=array(
                'message' => 'Your Registration is successful',
                'alert-type' => 'success'
            );
            return redirect('/')->with($notification);
           
    }

        



    }
    
    public function success_page()
    {
        return view('templete_two.registration.success_page');
    }

    public function customerSubmitLoginForm(Request $request){
        $this->validate($request, [
        'email' => 'required',
        'password' => 'required',
        ]);
        
        $email = $request->email;
        $password = md5($request->password);
        $result = Customer::where('email', $email)->where('password', $password)->first();
        if ($result) {
            Session::put('user_id', $result->id);
            Session::put('user_type', 'customer');
            Session::put('name', $result->first_name);
           	return redirect('/');
        }else{
            session()->flash('login_failed', "Information doesn't match !! ");
            return redirect()->back();
        } 
    }
    
    public function customerlogout(){   
         Session::flush();
        return Redirect('/');
    }



    public function facebookLogin(Request $r) {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookCallback(Request $r) {
    $user = Socialite::driver('facebook')->user();
        
        $customer = Customer::where('facebook_id', $user->id)->first();
        // if exiting user then login only.
        if(!empty($customer)) {
            // dd('not empty');
            $this->loginCustomer($customer);
            return redirect('/');
            
        }
        
        // if new user, then register then login as a customner
        if(empty($customer)) {

            $c_new = new Customer();
            $c_new->first_name = $user->user['name'];
            $c_new->email = $user->user['email'];
       
            $c_new->save();
            
            // now login
            $this->loginCustomer($c_new);
           	return redirect('/profile');
        }
        
        // somthing wrong msg
        return redirect('/customerLogin');
    }
    
    
    public function googleLogin(Request $r) {
        return Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $r) {
        $user = Socialite::driver('google')->user();

        $customer = Customer::where('google_id', $user->id)->first();
        // if exiting user then login only.
        if(!empty($customer)) {
            $this->loginCustomer($customer);
            return redirect('/');
        }
        
        
        // if new user, then register then login as a customner
        if(empty($customer)) {
            // create new customer
            $c_new = new Customer();
            $c_new->first_name = $user->user['name'];
            $c_new->email = $user->user['email'];
            $c_new->status = 1;
            $c_new->save();
            // now login
            $this->loginCustomer($c_new);
           	return redirect('/profile');
        }
        
        // somthing wrong msg
        return redirect('/customerLogin');
    }
    
    
    private function loginCustomer($c) {
        Session::put('user_id', $c->id);
        Session::put('user_type', 'customer');
        Session::put('name', $c->first_name);
    }

    
}
