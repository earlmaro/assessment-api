<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Helpers\Traits\ApiResponseTrait;
use Validator;
use Image;

class TodoController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $todos = Todo::all();
            $todos = $todos->toArray();

            function cmp($a, $b){
              return strcmp($a["is_completed"], $b["is_completed"]);
            }

            usort($todos, "App\Http\Controllers\cmp");
        } catch (\Exception $e) {
            return $this->respondError('encountered an error, ' . (!config('app.debug') ? 'Unable to create a new todo item' : (' Error: ' . $e->getMessage())));
        }

        return $this->respondWithResource(new JsonResource([
            'todos' => $todos
        ]));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $v = Validator::make($request->all(), [
                'title' => 'required',
                'photo' => 'required',
            ]);

            if ($v->fails()) {
                return $this->respondError(new JsonResource([
                    'message' => $v->errors(),
                    'statusCode' => 422
                ]));
            }
            if($request->hasFile('photo')) {
                $image = Image::make($request->file('photo'));
                $imageName = time().'-'.$request->file('photo')->getClientOriginalName();
                $destinationPath = public_path('uploads/');
                $image->resize(60,60);
                $image->save($destinationPath.$imageName);
            }else{
                $imageName = 'todo-image.png';
            }

            $todo = new Todo();
            $todo->title = $request->title;
            $todo->photo = $imageName;
            $todo->save();
        } catch (\Exception $e) {
            return $this->respondError('encountered an error, ' . (!config('app.debug') ? 'Unable to create a new todo item' : (' Error: ' . $e->getMessage())));
        }

        return $this->respondWithResource(new JsonResource([
            'todo' => $todo
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function show(Todo $todo)
    {
        return $this->respondWithResource(new JsonResource([
            'todo' => $todo
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function edit(Todo $todo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Todo $todo)
    {
        try {
            $v = Validator::make($request->all(), [
                'title' => 'required',
            ]);

            if ($v->fails()) {
                return $this->respondError(new JsonResource([
                    'message' => $v->errors(),
                    'statusCode' => 422
                ]));
            }
            if($request->hasFile('photo')) {
                $image = Image::make($request->file('photo'));
                $imageName = time().'-'.$request->file('photo')->getClientOriginalName();
                $destinationPath = public_path('uploads/');
                $image->resize(100,100);
                $image->save($destinationPath.$imageName);
            }else{
                $imageName = $request->photo;
            }
            $todo->title = $request->title;
            $todo->photo = $imageName;
            $todo->is_completed = $request->is_completed;
            $todo->update();
        } catch (\Exception $e) {
            return $this->respondError('encountered an error, ' . (!config('app.debug') ? 'Unable to create a new todo item' : (' Error: ' . $e->getMessage())));
        }

        return $this->respondWithResource(new JsonResource([
            'todo' => $todo
        ]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Todo  $todo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Todo $todo)
    {
        try {
            $todo->delete();
        } catch (\Exception $e) {
            return $this->respondError('encountered an error, ' . (!config('app.debug') ? 'Unable to create a new todo item' : (' Error: ' . $e->getMessage())));
        }
        return $this->respondSuccess(new JsonResource([
            'message' => "Resources has been deleted"
        ]));
    }
}
