<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * List all tasks for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->user()->tasks()->orderBy('order')->orderBy('created_at', 'desc');

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('priority')) {
                $query->where('priority', $request->input('priority'));
            }

            $tasks = $query->get()->map(fn (Task $task) => $this->formatTask($task));

            return response()->json([
                'success' => true,
                'message' => 'Tasks retrieved successfully',
                'data' => $tasks,
                'total_count' => $tasks->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tasks',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single task.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $task = $request->user()->tasks()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Task retrieved successfully',
                'data' => $this->formatTask($task),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new task.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['status'] = $data['status'] ?? Task::STATUS_PENDING;
            $data['priority'] = $data['priority'] ?? Task::PRIORITY_MEDIUM;

            $task = $request->user()->tasks()->create($data);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $this->formatTask($task),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, string $id): JsonResponse
    {
        try {
            $task = $request->user()->tasks()->findOrFail($id);
            $data = $request->validated();

            if (isset($data['status']) && $data['status'] === Task::STATUS_COMPLETED && !$task->completed_at) {
                $data['completed_at'] = now();
            }
            if (isset($data['status']) && $data['status'] !== Task::STATUS_COMPLETED) {
                $data['completed_at'] = null;
            }

            $task->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $this->formatTask($task->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $task = $request->user()->tasks()->findOrFail($id);
            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format task for API response.
     */
    private function formatTask(Task $task): array
    {
        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date?->toDateString(),
            'completed_at' => $task->completed_at?->toIso8601String(),
            'order' => $task->order,
            'created_at' => $task->created_at,
            'updated_at' => $task->updated_at,
        ];
    }
}
