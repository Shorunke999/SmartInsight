<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use App\Models\User;
use Illuminate\Http\Request;

class AutobotController extends Controller
{
    /**
     * @api {get} /autobots Fetch a list of Autobots
     * @apiName GetAutobots
     * @apiGroup Autobots
     *
     * @apiSuccess {Object[]} autobots List of Autobots
     */
    public function index()
    {
        $autobots = User::paginate(10);
        return response()->json($autobots);
    }

    /**
     * @api {get} /autobots/:id/posts Fetch posts for a specific Autobot
     * @apiName GetAutobotPosts
     * @apiGroup Autobots
     *
     * @apiParam {integer} id The ID of the Autobot.
     * @apiSuccess {Object[]} posts List of posts.
     */
    public function posts($id)
    {
        $autobot_user = User::findOrFail($id);
        $posts = $autobot_user->posts()->paginate(10);
        return response()->json($posts,200);
    }

    /**
     * @api {get} /posts/:id/comments Fetch comments for a specific post
     * @apiName GetPostComments
     * @apiGroup Posts
     *
     * @apiParam {integer} id Post ID
     * @apiSuccess {Object[]} comments List of comments
     */
    public function comments($post_id)
    {
        $post = Posts::findOrFail($post_id);
        $comments = $post->comments()->paginate(10);
        return response()->json($comments,200);
    }
}

