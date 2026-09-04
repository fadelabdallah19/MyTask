<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * List the authenticated user's tasks (paginated).
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $request->user()->tasks()->latest()->paginate(10);

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Show a single task owned by the authenticated user.
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $task = $request->user()->tasks()->findOrFail($task->id);

        return (new TaskResource($task))->response();
    }
}
