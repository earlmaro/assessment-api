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
        $todos = Todo::all();

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
        if($request->file('photo')) {
            $image = Image::make($request->file('photo'));
            $imageName = time().'-'.$request->file('photo')->getClientOriginalName();
            $destinationPathThumbnail = public_path('uploads/');
            $image->resize(100,100);
            $image->save($destinationPathThumbnail.$imageName);
        }

        $todo = new Todo();
        $todo->title = $request->title;
        $todo->photo = $imageName;
        $todo->save();

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
        // return $request;
        $todo->title = $request->title;
        $todo->photo = $request->photo;
        $todo->is_completed = $request->is_completed;
        $todo->update();
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
        $todo->delete();
        return $this->respondSuccess(new JsonResource([
            'message' => "Resources has been deleted"
        ]));
    }
}
