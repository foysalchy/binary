<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\EmailSetting;
use App\Models\IndividualMail;
use Illuminate\Http\Request;
use Mail;
use Session;

class EmailMarketingController extends Controller
{
    public function customer_email(){
        $customer_mail = Customer::orderBy('id', 'desc')->get();
        return view('backend.admin.email.email_list',compact('customer_mail'));
    }
    public function marketing_email(Request $request){
        $validatedData = $request->validate([
	        'id' => 'required',

	    ],[
	    	'id.required' => 'Select Product First!!',
	    ]);

    	// $all_product = $request->id;
		$emails = $request->id;

            Mail::send('backend.admin.email.template', [], function($message) use ($emails)
            {    
                $data = EmailSetting::first();
                if(!empty($data->subject)){
                    $message->to($emails)->subject($data->subject); 
                }else{
                    $message->to($emails)->subject('This is test Mail'); 
                }
            });

            $notification=array(
                'message' => 'Email Send.Please Check Your Email',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
    }
    public function email_template(){
        return view('backend.admin.email.details');
    }
    public function email_details(Request $request){
        $validatedData = $request->validate([
	        'subject' => 'required',
	        'description' => 'required',

	    ]);
        $data = EmailSetting::first();
        if($data){
            $details =EmailSetting::first();
            $details->subject = $request->subject;
            $details->description = $request->description;
            $details->save();
            $notification=array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }else{
            $details =new EmailSetting ();
            $details->subject = $request->subject;
            $details->description = $request->description;
            $details->save();
            $notification=array(
                'message' => 'Successfully save',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }
    public function individual_email(){
        $customer_mail = Customer::orderBy('id', 'desc')->get();
        return view('backend.admin.email.individual_email',compact('customer_mail'));
    }
    public function send_individual_mail($id){
        $customer_mail = Customer::orderBy('id', 'desc')->where('id',$id)->first();
        return view('backend.admin.email.in_mail_details',compact('customer_mail'));
    }
    public function save_individual_email(Request $request){
        $customer = Customer::find($request->customer_id);
        $name = $customer->first_name.' '.$customer->last_name;
        $email = $customer->email;
        $subject = $request->subject;
        $description = $request->description;
        $data =new IndividualMail();
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
