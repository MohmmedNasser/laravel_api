<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['posts' => Post::with('user')->latest()->get()], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post = $request->user()->posts()->create($validated);

        // $post = Post::create([
        //     'title' => $validated['title'],
        //     'body' => $validated['body'],
        //     'user_id' => $request->user()->id,
        // ]);

        return response()->json([
            'message' => 'Post created successfully',
            'post' => $post,
            'author' => $post->user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return response()->json(['post' => $post->load('user')], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        // $this->authorize('modify', $post);
        Gate::authorize('modify', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post->update($validated);

        return response()->json(['message' => 'Post updated successfully', 'post' => $post], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {

        Gate::authorize('modify', $post);

        $post->delete;

        return response()->json(['message' => 'Post deleted successfully'], 200);
    }
}
