<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\PurPOResource;

class PurPOController extends Controller
{
    public function index()
    {
        $posts = Post::all(); // Retrieve all posts
        $formattedPosts = PostResource::collection($posts); // Format the posts using the resource
        return view('posts.index', ['posts' => $formattedPosts]); // Pass formatted data to view
    }

    // Show a specific post
    public function show($id)
    {
        $post = Post::findOrFail($id); // Find the post by id
        $formattedPost = new PostResource($post); // Format the post using the resource
        return view('posts.show', ['post' => $formattedPost]); // Pass formatted post to view
    }

    // Store a new post
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Create the new post
        $post = Post::create($validated);

        // Redirect with a success message
        return redirect()->route('posts.index')->with('success', 'Post created successfully!');
    }

    // Edit a post
    public function edit($id)
    {
        $post = Post::findOrFail($id); // Retrieve the post by id
        return view('posts.edit', ['post' => $post]); // Pass the post to the edit view
    }

    // Update a post
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id); // Find the post by id

        // Validate and update the post
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);
        
        $post->update($validated); // Update the post with the new data
        
        return redirect()->route('posts.index')->with('success', 'Post updated successfully!');
    }

    // Delete a post
    public function destroy($id)
    {
        $post = Post::findOrFail($id); // Find the post by id
        $post->delete(); // Delete the post
        
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully!');
    }
}
