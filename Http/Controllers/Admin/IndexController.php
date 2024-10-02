<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Setting;
use App\Models\Question;
use App\Models\Result;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Quiz;

class IndexController extends Controller
{
    public function index(){
        return view('admin.index');
    }
    public function home(){
        $setting = Setting::where('page','play')->first();
        //dd($setting);
        $quizes = Quiz::where('status',1)->paginate(10);
       // dd($quizes);
        return view('frontend.quiz',compact('setting','quizes'));
    }
   
    public function play($slug){
        // $limit = 10;
        // $availQuestionSet = [10,20,30,40,50,80,100,120,150,200];
        // if(!empty($request['questions'])){
        //     $limit = $request['questions'];
        //     if(!in_array($limit,$availQuestionSet)){
        //         return redirect('/play');
        //     }
        // }
      //dd($slug);
        $setting = Setting::where('page','play')->first();
        // $questions = Question::where('status','active')->take($limit)->inRandomOrder()->get();
        // session()->put('quiz_random_question',$questions);
        $quizes = Quiz::with('question')->where('slug',$slug)->where('status',1)->first();
       //dd($quizes);
        session()->put('quiz_random_question',$quizes->question);
        return view('play',compact('setting','quizes'));
    }

    public function result(Request $request){
      //dd($request);
    if(!empty($request->myanswer)){
        $questions = "";
        $answers = "";
        $resultID = "";
        if($request->isMethod('post')){
            if(isset($request->myanswer) && !empty($request->myanswer) && $request->play == 1){
                $answers = $request->myanswer;
                //dd($answers);
                $ids = [];
                foreach($answers as $key => $value){array_push($ids,$key);}
                $str_arr = array_values($ids); 
               // dd($str_arr);
                session()->put('quiz_random_answer',$answers);
                // create answer
                $device = $request->header('User-Agent');
                $platform = array('data-type'=>'User-Agent','data-value'=>$device);
                $request['platform'] = json_encode($platform);
                $user = Auth::user();
                //dd($user);
                if($user){$request['user_id'] = $user['id'];}
                $questions = session()->get('quiz_random_question');
                //dd($questions);
                $total_questions = 0;
                $correct_answer = 0;
                $wrong_answer = 0;
                $no_answer = 0;
                foreach($questions as $question){
                    if(isset($answers[$question->id])){
                        $answer = $answers[$question->id];
                        if($answer == $question->bestoption){
                            $correct_answer++;
                        }else{
                            $wrong_answer++;
                        }
                    }else{
                        $answer = "";
                        $no_answer++;
                    }
                    $total_questions++;
                }
                $request['questiontotal'] = $total_questions;
                $request['correctanswer'] = $correct_answer;
                $request['totalmark'] = $correct_answer * 10;
                $request['wronganswer'] = $wrong_answer;
                $request['noanswer'] = $no_answer;
                $answersheet = array('data-questions'=>$questions,'data-answer'=>$answers);
                $request['answersheet'] = json_encode($answersheet);
                $request['identification'] = md5(uniqid(true));
    
                $validated = $request->validate([
                    'name'=>[],
                    'phone'=>[],
                    'email'=>[],
                    'user_id'=>[],
                    'quiz_id'=>[],
                    'platform'=>[],
                    'totalmark'=>[],
                    'questiontotal'=>['required'],
                    'correctanswer'=>['required'],
                    'wronganswer'=>[],
                    'noanswer'=>[],
                    'answersheet'=>[],
                    'identification'=>[]
                ]);
                  //dd($validated);
                 //dd(Session::get('name'));
                $resutl = Result::create($validated);
                $resultID = $resutl->id;
                session()->put('quiz_random_id', $resultID);
                return redirect('/result/'.$resultID);
                // dd('here I am !');
            }
        }
        
        $resultID = session()->get('quiz_random_id');
        $questions = session()->get('quiz_random_question');
        $answers = session()->get('quiz_random_answer');
        $setting = Setting::where('page','play')->first();
        return view('result',compact('setting','questions','answers','resultID'));
        }
        else{
            return redirect()->back()->with('warning','Please Select any of the following Options!!!');
        }
    }

    public function resultview($id){
        $result = Result::where('id',$id)->first();
        $setting = Setting::where('page','play')->first();
        return view('resultview',compact('setting','result'));
    }

    public function results(){
        $user = Auth::user();
        if($user){$user_id = $user['id'];}
        $results = Result::where('user_id',$user_id)->orderBy('id','desc')->paginate(10);
        $setting = Setting::where('page','home')->first();
        return view('user_results',compact('results','setting'));
    }
    public function topics(){
        // $user = Auth::user();
        // if($user){$user_id = $user['id'];}
        // $results = Result::where('user_id',$user_id)->orderBy('id','desc')->paginate(10);
        $topics = '';
        $setting = Setting::where('page','topics')->first();
        return view('topics',compact('topics','setting'));
    }

    public function store_participant(Request $request){   
        // dd($request->path());
        $slug = $request->slug;
        $name = $request->name;
        $phone = $request->phone;
        $email = $request->email;
        Session::put('name',$name);
        Session::put('phone',$phone);
        Session::put('email',$email);
      //  return redirect()->route('play/technical-quiz-2');
        return \Redirect::route('play', $slug);
    }

    public function home_details($slug){
        //dd($slug);
        $quize = Quiz::where('slug',$slug)->where('status',1)->first();
        return view('frontend.quiz_details',compact('quize'));
    }
}
