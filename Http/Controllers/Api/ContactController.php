<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    public function contact(Request $request)
    {
        $name = 'asdas';
        $email = 'asd@gmail.com';
        $subject = 'asdsadasd';
        $message = 'asdasdasdsadad';

        $data = new Contact();
        $data->name = $name;
        $data->email = $email;
        $data->subject = $subject;
        $data->message = $message;
        $data->save();

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $data,
        ]);
    }
}
