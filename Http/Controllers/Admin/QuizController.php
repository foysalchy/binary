<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Result;
use DB;
use Illuminate\Support\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index(){
        $quizzes = Quiz::orderBy('id','desc')->get();
        return view('admin.quizzes.index',compact('quizzes'));
    }
    
    public function create(){
        return view('admin.quizzes.create');
    }

    public function show($id)
    {

       // dd($id);
       // $results= Result::orderBy('totalmark','desc')->orderBy('created_at','asc')->paginate(10);  
       $results = Result::where('quiz_id',$id)->orderBy('totalmark','desc')->orderBy('created_at','asc')->paginate(10);
     //  dd($result);
       return view('admin.quizzes.view',compact('results'));
    }

    public function store(Request $request){
        //dd($request);
        // $validated = $request->validate([
        //     'name'=>['required'],
        //     'title'=>['required'],
        // ]);
        // $request->user()->quizzes()->create($validated);
        $validated = $request->validate([
            'name' => 'required',
            'title' => 'required',
        ]);
        // dd($request->q);
        $quiz = new Quiz;
        $quiz->quiztype = 'instant';
        $quiz->name = $request->name;
        $quiz->title = $request->title;
        $quiz->slug = Str::slug($request->title);
        $quiz->description=  $request->description;
        $image = $request->file('file');
        //dd($image);
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/quiz/';
            $image_url = $image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            $sizes = [200, 480, 800, 1600];
            $size_name = ['t', 's', 'm', 'l'];
            for($i = 0; $i < 4; $i++) {
                $image = Image::make($upload_path. $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($upload_path .$size_name[$i].'/'. $image_full_name);
            }
            if($success)
            {
                $quiz->image = $image_url;
            }
        }

        $quiz->start_at = $request->start_at;
        $quiz->end_at = $request->end_at;
        $quiz->status = $request->status;
        if($quiz->save()){
            if(!empty($request->q)){
                $multiquiz= $request->q;
                foreach($multiquiz as $key => $item){
                    $question= new Question;
                    $question->questiontext = $item['questiontext'];
                    $question->optionfirst = $item['optionfirst'];
                    $question->optionsecond = $item['optionsecond'];
                    $question->optionthird = $item['optionthird'];
                    $question->optionfourth = $item['optionfourth'];
                    $question->bestoption = $item['bestoption'];
                    $question->expiring = 0;
                    $question->user_id = 5;
                    $question->save();
                
                    DB::table('quiz_question')->insert(
                        [
                            'question_id' => $question->id,
                            'quiz_id' => $quiz->id,
                            "created_at" =>  \Carbon\Carbon::now(),
                            "updated_at" => \Carbon\Carbon::now()
                        ]
                    );          
                }
            }
        }
        return redirect('admin/quizzes')->with('message', 'Quizze with Question created!'); 
    }
    public function edit(Quiz $quiz){

        $quiz = Quiz::with('question')->find($quiz->id);
        return view('admin.quizzes.edit',compact('quiz'));
    }
    public function update(Request $request, Quiz $quiz){
       
        $quiz = Quiz::find($quiz->id);;
        $quiz->quiztype = 'instant';
        $quiz->name = $request->name;
        $quiz->title = $request->title;
        $quiz->slug = Str::slug($request->title);
        $quiz->description=  $request->description;
        $image = $request->file('file');
        //dd($image);
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/quiz/';
            $image_url = $image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            $sizes = [200, 480, 800, 1600];
            $size_name = ['t', 's', 'm', 'l'];
            for($i = 0; $i < 4; $i++) {
                $image = Image::make($upload_path. $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($upload_path .$size_name[$i].'/'. $image_full_name);
            }
            if($success)
            {
                $quiz->image = $image_url;
            }
        }

        $quiz->start_at = $request->start_at;
        $quiz->end_at = $request->end_at;
        $quiz->status = $request->status;
        if($quiz->save()){
            if(!empty($request->q)){
                $multiquiz= $request->q;            
                foreach($multiquiz as $key => $item){
                    // dd($item);
                    if(!empty($item['id'])){
                        $question = Question::find($item['id']);
                        $question->questiontext = $item['questiontext'];
                        $question->optionfirst = $item['optionfirst'];
                        $question->optionsecond = $item['optionsecond'];
                        $question->optionthird = $item['optionthird'];
                        $question->optionfourth = $item['optionfourth'];
                        $question->bestoption = $item['bestoption'];
                        $question->save();
                    }
                    else{                     
                        $question= new Question;
                        $question->questiontext = $item['questiontext'];
                        $question->optionfirst = $item['optionfirst'];
                        $question->optionsecond = $item['optionsecond'];
                        $question->optionthird = $item['optionthird'];
                        $question->optionfourth = $item['optionfourth'];
                        $question->bestoption = $item['bestoption'];
                        $question->expiring = 0;
                        $question->user_id = 5;
                        $question->save();
                    } 
                }
            }
        }
        return redirect('admin/quizzes')->with('message', 'Quiz with Question Updated!'); 
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //dd('delete');
        Quiz::find($id)->delete();
        DB::table('quiz_question')->where('id', $id)->delete();
        return redirect()->back();
    }
}
