<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    // bad: no Form Request classes, validation duplicated and inline

    public function index()
    {
        $tags = Tag::all();
        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    public function show($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['success' => false, 'message' => 'Tag not found'], 404);
        }
        return response()->json(['success' => true, 'data' => $tag]);
    }

    public function store(Request $request)
    {
        // bad: validation rules duplicated, no centralized rules
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }
        $t = new Tag();
        $t->name = $request->input('name');
        $t->slug = $request->input('slug');
        $t->color = $request->input('color');
        $t->save();
        return response()->json(['success' => true, 'data' => $t]); // bad: 200 instead of 201
    }

    public function update(Request $request, $id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['success' => false, 'message' => 'Tag not found'], 404);
        }
        $v = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed']); // bad: no 422, no errors in body
        }
        if ($request->has('name')) {
            $tag->name = $request->name;
        }
        if ($request->has('slug')) {
            $tag->slug = $request->slug;
        }
        if ($request->has('color')) {
            $tag->color = $request->color;
        }
        $tag->save();
        return response()->json(['success' => true, 'tag' => $tag]); // bad: inconsistent key "tag" vs "data"
    }

    public function destroy($id)
    {
        $tag = Tag::find($id);
        if (!$tag) {
            return response()->json(['success' => false, 'message' => 'Tag not found'], 404);
        }
        $tag->delete();
        return response()->json(['success' => true]);
    }

    // bad: extra method using raw query for no reason, mixed with Eloquent
    public function getTagCount()
    {
        $c = DB::table('tags')->count();
        return response()->json(['count' => $c]);
    }
}
