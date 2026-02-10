<?php

namespace App\Http\Controllers\Api;

use App\Models\SmartList;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SmartListRequest;
use App\Http\Resources\Api\SmartListResource;

class SmartListController extends Controller
{
    public function index(Request $request)
    {
        $smartLists = SmartList::where('user_id', $request->user()->id)->with('meals')->get();
        return response()->json([
            'success' => true,
            'message' => 'Smart lists retrieved successfully',
            'data' => SmartListResource::collection($smartLists),
        ]);
    }
    public function store(SmartListRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['description'] = $data['description'] ?? '';
        $mealIds = $data['meal_ids'] ?? [];
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }
        $smartList = SmartList::create($data);
        if (!empty($mealIds)) {
            $smartList->meals()->attach($mealIds);
        }
        $smartList->load('meals');

        return response()->json([
            'success' => true,
            'message' => 'Wish list created successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function show(Request $request, $id)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->with('meals')->findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Smart list retrieved successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }
    public function update(SmartListRequest $request, $id)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $data = $request->validated();
        if (array_key_exists('description', $data) && $data['description'] === null) {
            $data['description'] = '';
        }
        $mealIds = $data['meal_ids'] ?? null;
        unset($data['meal_ids']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }
        $smartList->update($data);
        if ($mealIds !== null) {
            $smartList->meals()->sync($mealIds);
        }
        $smartList->load('meals');

        return response()->json([
            'success' => true,
            'message' => 'Wish list updated successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $smartList->meals()->detach();
        $smartList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wish list deleted successfully',
        ]);
    }

    /**
     * Add a meal to a wish list.
     */
    public function addMeal(Request $request, string $id)
    {
        $request->validate(['meal_id' => ['required', 'exists:meals,id']]);
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $smartList->meals()->syncWithoutDetaching([$request->meal_id]);
        $smartList->load('meals');
        return response()->json([
            'success' => true,
            'message' => 'Item added to wish list successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }

    /**
     * Remove a meal from a wish list.
     */
    public function removeMeal(Request $request, string $id, string $mealId)
    {
        $smartList = SmartList::where('user_id', $request->user()->id)->findOrFail($id);
        $smartList->meals()->detach($mealId);
        $smartList->load('meals');
        return response()->json([
            'success' => true,
            'message' => 'Item removed from wish list successfully',
            'data' => new SmartListResource($smartList),
        ]);
    }
}
