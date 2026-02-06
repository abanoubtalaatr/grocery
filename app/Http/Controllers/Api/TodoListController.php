<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTodoListRequest;
use App\Http\Requests\UpdateTodoListRequest;
use App\Models\TodoList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TodoListController extends Controller
{
    /**
     * List all todo items for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = $request->user()->todoLists()->orderBy('order')->orderBy('created_at', 'desc');

            if ($request->has('completed')) {
                $query->where('completed', $request->boolean('completed'));
            }

            $todoLists = $query->get()->map(fn (TodoList $item) => $this->formatTodo($item));

            return response()->json([
                'success' => true,
                'message' => 'Todo list retrieved successfully',
                'data' => $todoLists,
                'total_count' => $todoLists->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve todo list',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single todo item.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $todo = $request->user()->todoLists()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Todo item retrieved successfully',
                'data' => $this->formatTodo($todo),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Todo item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve todo item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new todo item.
     */
    public function store(StoreTodoListRequest $request): JsonResponse
    {
        try {
            $todo = $request->user()->todoLists()->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Todo item created successfully',
                'data' => $this->formatTodo($todo),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create todo item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a todo item.
     */
    public function update(UpdateTodoListRequest $request, string $id): JsonResponse
    {
        try {
            $todo = $request->user()->todoLists()->findOrFail($id);
            $todo->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Todo item updated successfully',
                'data' => $this->formatTodo($todo->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Todo item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update todo item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a todo item.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $todo = $request->user()->todoLists()->findOrFail($id);
            $todo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Todo item deleted successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Todo item not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete todo item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format todo item for API response.
     */
    private function formatTodo(TodoList $todo): array
    {
        return [
            'id' => $todo->id,
            'title' => $todo->title,
            'description' => $todo->description,
            'completed' => $todo->completed,
            'due_date' => $todo->due_date?->toDateString(),
            'order' => $todo->order,
            'created_at' => $todo->created_at,
            'updated_at' => $todo->updated_at,
        ];
    }
}
