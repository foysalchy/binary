<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index(){
        $settings = Setting::all();
        return view('admin.settings.index',compact('settings'));
    }
    
    public function create(){
        return view('admin.settings.create');
    }

    public function store(Request $request){
        // dd($request);
        // $setting = new Setting();
        $validated = $request->validate([
            'name'=>['required'],
            'page'=>['required'],
            'theme'=>['required'],
            'title'=>['required'],
            'description'=>['required'],
            'image'=>['required'],
        ]);
        // dd($validated);
        Setting::create($validated);
        // $setting->save();
        return to_route('admin.settings.index');
    }

    public function edit(Setting $setting){
        return view('admin.settings.edit',compact('setting'));
    }

    public function update(Request $request, Setting $Setting){
        $validated = $request->validate([
            'name'=>['required'],
            'page'=>['required'],
            'theme'=>['required'],
            'title'=>['required'],
            'description'=>['required'],
            'image'=>['required'],
        ]);
        $Setting->update($validated);

        return to_route('admin.settings.index');
    }

    // public function destroy(Setting $Setting){
    //     $Setting->delete();
    //     return back()->with('message','Setting deleted successfully');
    // }
}
