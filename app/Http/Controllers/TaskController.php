<?php

namespace App\Http\Controllers;

use App\Http\Requests\StroreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Expr\Cast\String_;

class TaskController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('user')->where('user_id', Auth::user()->user_id)->get();
        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StroreTaskRequest $request)
    {
        $request->validated($request->all());
        $task = Task::create([
            'user_id' => Auth::user()->user_id,
            'task_name' => $request->task_name,
            'priority' => $request->priority,
            'description' => $request->description
        ]);
        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return $this->isNotAuthorized($task) ? $this->isNotAuthorized($task) : new TaskResource($task);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($this->isNotAuthorized($task)) {
            return $this->isNotAuthorized($task);
        }
        $task->update($request->all());
        return new TaskResource($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($this->isNotAuthorized($task)) {
            return $this->isNotAuthorized($task);
        }
        $task->delete();
        return response()->noContent(); // Returns a 204 No Content response
    }


    private function isNotAuthorized($task)
    {
        if (Auth::user()->user_id != $task->user_id) {
            return $this->error('', 'You are not Authorized to make this request.', 403);
        }
        return null;
    }
}
