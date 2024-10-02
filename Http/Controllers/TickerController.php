<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;

class TickerController extends Controller
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
            $tickers = DB::table('ticker')->get();
            return view('backend.admin.ticker.index', compact('tickers'));
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
        $user_id = Auth::user();
        if($user_id){
            return view('backend.admin.ticker.create');
        }else{
            return redirect('login');
        }   
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       // dd($request->all());
        DB::table('ticker')->insert([
            'name' => $request->name,
            'ticker_des' => $request->ticker_des,
            'status' => $request->status,
            'created_at' => now()
        ]);
        return redirect('admin/ticker');
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
        $user_id = Auth::user();
        if($user_id){
            $ticker = DB::table('ticker')->find($id);
            return view('backend.admin.ticker.edit', compact('ticker'));
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
      //  dd($request->all());
        $result = DB::table('ticker')
        ->where('id', $id)
        ->update([
            'name' => $request->name,
            'ticker_des' => $request->ticker_des,
            'status' => $request->status,
            'updated_at' => now()
        ]);

    return redirect('admin/ticker');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table('ticker')->where('id', $id)->delete();
        $notification=array(
            'message' => 'Ticker Deleted Successfully !!',
            'alert-type' => 'error'
        );

    return redirect()->back()->with($notification);
    }
}
