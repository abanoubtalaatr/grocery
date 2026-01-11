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
        
        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }
        $smartList = SmartList::create($data);
        $smartList->meals()->attach($request->meal_ids);


        return response()->json([
            'success' => true,
            'message' => 'Smart list created successfully',
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
        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/smart-lists'), $imageName);
            $data['image'] = $imageName;
        }

        $smartList->update($data);
        $smartList->meals()->sync($request->meal_ids);
        return response()->json([
            'success' => true,
            'message' => 'Smart list updated successfully',
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
            'message' => 'Smart list deleted successfully',
        ]);
    }
}
