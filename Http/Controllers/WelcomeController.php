<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Session;
use Illuminate\Support\Carbon;
use DB;
use Auth;

class WelcomeController extends Controller
{
    public function logout(){
        $user = Auth::user();
        Session::put('user', $user);
        $user = Session::get('user');
        $role_id = $user->role_id;
        $name = $user->name;
        $email = $user->email;
        $dt = Carbon::now();
        $todayDate = $dt->toDayDateTimeString();
       $ip =   request()->ip();
      
        $activityLog = [
            'role_id' => $role_id,
            'name' => $name,
            'email' => $email,
            'description' => 'Log Out',
            'ip' => $ip,
            'time' => $todayDate

        ];
        DB::table('activity_log')->insert($activityLog);
        Session::flush();
        return Redirect('/');
    }
}
