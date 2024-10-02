<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{
    public function index(){
        $questions = Question::orderBy('id','DESC')->paginate(20);
        return view('admin.questions.index',compact('questions'));
    }
    
    public function create(){
        return view('admin.questions.create');
    }

    public function store(Request $request){
      //  dd($request);
        // dd($request->uploadquestion);
        if(!empty($request->uploadquestion)){
            $questions = json_decode(file_get_contents('questions/data.txt'),true);
            // dd($questions);
            $i = 0;
            foreach($questions as $question){
            // dd($question['questiontext']);
                if( !empty($question['questiontext']) && 
                    !empty($question['option1']) && 
                    !empty($question['option2']) && 
                    !empty($question['option3']) && 
                    !empty($question['option4']) && 
                    !empty($question['answer'])
                ){
                    $request['questiontext'] = $question['questiontext'];
                    $request['optionfirst'] = $question['option1'];
                    $request['optionsecond'] = $question['option2'];
                    $request['optionthird'] = $question['option3'];
                    $request['optionfourth'] = $question['option4'];
                    
                    if($question['answer'] == $question['option1']){
                        $bestoption = "optionfirst";
                    }elseif($question['answer'] == $question['option2']){
                        $bestoption = "optionsecond";
                    }elseif($question['answer'] == $question['option3']){
                        $bestoption = "optionthird";
                    }elseif($question['answer'] == $question['option4']){
                        $bestoption = "optionfourth";
                    }

                    $request['bestoption'] = $bestoption;
                    $request['status'] = "active";
                    $request['questionlabel'] = "easy";
                    $request['expiring'] = 0;
                    unset($request['uploadquestion']);
                    // dd($request);
                    $validated = $request->validate([
                        'questiontext'=>['required'],
                        'optionfirst'=>['required'],
                        'optionsecond'=>['required'],
                        'optionthird'=>['required'],
                        'optionfourth'=>['required'],
                        'bestoption'=> ['required'],
                        'questionlabel'=> ['required'],
                        'status'=>['required'],
                        'expiring'=>['required']
                    ]);
                    // dd($request);
                    $request->user()->questions()->create($validated);
                }
                
                $i++;
                // dd('1 added');
            }
            // dd($questions);
            dd('file found q saved');
        }else{
        // dd('file not found');
        // $request['questiontext'] = "Test question one";
        // $request['optionfirst'] = "one";
        // $request['optionsecond'] = "twp";
        // $request['optionthird'] = "three";
        // $request['optionfourth'] = "four";
        // $request['bestoption'] = "optionsecond";
        // $request['questionlabel'] = "easy";
        $request['expiring'] = 0;
        //dd($request);
        $validated = $request->validate([
            'questiontext'=>['required'],
            'optionfirst'=>['required'],
            'optionsecond'=>['required'],
            'optionthird'=>['required'],
            'optionfourth'=>['required'],
            'bestoption'=> ['required'],
            'questionlabel'=> ['required'],
            'image'=>['required'],
            'status'=>['required'],
            'expiring'=>['required']
        ]);
        $request->user()->questions()->create($validated);
        
        }
        return redirect('admin/questions')->with('message', 'Question created!');
    }

    public function edit(Question $question){
        
        return view('admin.questions.edit',compact('question'));
    }

    public function update(Request $request, Question $question){
        // dd($request->all());
        // $request['questiontext'] = "Test question one";
        // $request['optionfirst'] = "one";
        // $request['optionsecond'] = "twp";
        // $request['optionthird'] = "three";
        // $request['optionfourth'] = "four";
        // $request['bestoption'] = "optionsecond";
        // $request['questionlabel'] = "easy";
        $request['expiring'] = 0;
        $validated = $request->validate([
            'questiontext'=>['required'],
            'optionfirst'=>['required'],
            'optionsecond'=>['required'],
            'optionthird'=>['required'],
            'optionfourth'=>['required'],
            'bestoption'=> ['required'],
            'questionlabel'=> ['required'],
            'image'=>['required'],
            'status'=>['required'],
            'expiring'=>['required']
        ]);
        $question->update($validated);
        return redirect('admin/questions')->with('message', 'Question Updated!');
    }
}
