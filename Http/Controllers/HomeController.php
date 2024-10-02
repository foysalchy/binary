<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Auth;
use Illuminate\Support\Carbon;
use DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {   
     $user= Auth::user();
      if($user->status == 0){
        return redirect('/login');
      }
      else{
        $user = Auth::user();
        Session::put('user', $user);
        $user = Session::get('user');
        $role_id = $user->role_id;
        $name = $user->name;
        $email = $user->email;
        $dt = Carbon::now();
        $todayDate = $dt->toDayDateTimeString();
        $ip = request()->ip();
        $activityLog = [
          'role_id' => $role_id,
            'name' => $name,
            'email' => $email,
            'description' => 'Has log in',
            'ip' => $ip,
            'time' => $todayDate

        ];
        DB::table('activity_log')->insert($activityLog);
        return view('home');
      }
    }


}
