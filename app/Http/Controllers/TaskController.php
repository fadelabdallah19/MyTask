<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the user's tasks.
     */
    public function index(Request $request): View|Response
    {
        $tasks = $this->filteredTaskQuery($request)->paginate(8)->withQueryString();

        if ($request->boolean('partial')) {
            return response(view('tasks._table', compact('tasks')));
        }

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Build the query for the current user's tasks with filters and sorting.
     */
    private function filteredTaskQuery(Request $request): HasMany
    {
        return auth()->user()->tasks()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status'));
            })
            ->when($request->filled('priority'), function ($query) use ($request) {
                $query->where('priority', $request->string('priority'));
            })
            ->when($request->filled('sort'), function ($query) use ($request) {
                match ($request->string('sort')) {
                    'oldest' => $query->oldest(),
                    'deadline' => $query->orderBy('deadline'),
                    'priority' => $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')"),
                    default => $query->latest(),
                };
            })
            ->latest();
    }

    /**
     * Show the form for creating a new task.
     */
    public function create(): View
    {
        return view('tasks.create');
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request): RedirectResponse
    {
        auth()->user()->tasks()->create($request->validated());

        return redirect()->route('tasks.index')->with('success', 'Task berhasil dibuat.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified task.
     */
    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', compact('task'));
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $task->update($request->validated());

        return redirect()->route('tasks.index')->with('success', 'Task diperbarui.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task dihapus.');
    }

    /**
     * Display the current user's soft-deleted (trashed) tasks.
     */
    public function trash(): View
    {
        $tasks = auth()->user()->tasks()
            ->onlyTrashed()
            ->latest('deleted_at')
            ->paginate(8)
            ->withQueryString();

        return view('tasks.trash', compact('tasks'));
    }

    /**
     * Restore a soft-deleted task.
     */
    public function restore(Task $task): RedirectResponse
    {
        $this->authorize('restore', $task);

        $task->restore();

        return back()->with('success', 'Task dipulihkan.');
    }

    /**
     * Permanently delete a soft-deleted task.
     */
    public function forceDelete(Task $task): RedirectResponse
    {
        $this->authorize('forceDelete', $task);

        $task->forceDelete();

        return back()->with('success', 'Task dihapus permanen.');
    }
}
