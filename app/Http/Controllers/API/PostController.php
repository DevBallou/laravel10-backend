<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $post = Post::when($request->search, function($query, $search) {
            $query->where('title', 'like', '%'.$search.'%');
        })->paginate(5);

        $data = PostResource::collection($post)->resource;

        return $this->sendResponse($data, 'Successfully', 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required',
                'description' => 'required',
            ]);

            $userId = Auth::user()->id;

            $data = Post::created([
                'title' => $request->title,
                'slug' => \Str::slug($request->slug),
                'description' => $request->description,
                'user_id' => $userId,
            ]);

            $result = new PostResource($data);

            return $this->sendResponse($result, 'Successfully', 200);
        } catch (Exception $e) {
            return $this->sendError('Error '. $e->getMessage(), null, 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $db = Post::where('slug', $id)->first();
        $result = new PostResource($db);

        return $this->sendResponse($result, 'Successfully', 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $db = Post::where('slug', $id)->first();

            $db->title = $request->title;
            $db->slug = \Str::slug($request->slug);
            $db->description = $request->description;

            $db->update();

            return $this->sendResponse(null, 'Successfully', 200);
        } catch (Exception $e) {
            return $this->sendError('Error '. $e->getMessage(), null, 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $db = Post::where('slug', $id)->first();
        if($db) {
            $db->delete();

            return $this->sendResponse(null, 'Successfully', 200);
        }
        return $this->sendError('Error ', null, 404);
    }
}
