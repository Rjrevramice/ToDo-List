<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ToDo;

class ToDoController extends Controller
{
    public function index(){
        $toDo = ToDo::orderBy('id','asc')->get();
        return view('todo.index',compact('toDo'));
    }

    public function store(Request $request) {
        $request->validate([
            'task' => 'required|string|max:255',
        ]);
    
        $existingTask = ToDo::where('task', $request->task)->first();
    
        if ($existingTask) {
            return response()->json(['error' => 'Task already exists'], 200);
        }
    
        $toDo = new ToDo;
        $toDo->task = $request->task;
        $toDo->save();
    
        return response()->json(['data' => $toDo], 201);
    }
    

    public function update(Request $request){
        $toDo = ToDo::where('id',$request->id)->first();
        $toDo->status = $request->status;
        $toDo->save();
        return response()->json(['data'=>$toDo],200);
    }

    public function delete(Request $request){
        $toDo = ToDo::where('id',$request->id)->first();
        if($toDo){
            $toDo->delete();
        }
        return true;
    }
}
