<?php

namespace App\Jobs;

use App\Events\BroadcastUserCountEvent;
use App\Models\Comments;
use App\Models\Posts;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateAutobotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Fetch users
            $users = $this->fetchUsers(0);

            // Insert new users, posts, and comments into the database within a transaction
            DB::transaction(function () use ($users) {
                $this->insertUsers($users);
            });

        } catch (\Exception $e) {
            // Detailed error handling and logging
            Log::info('Error in the CreateAutobotJob handle method',[
                'error' => $e->getMessage()]);
            $this->fail($e);
        }
    }


    private function fetchUsers(int $lastFetchedUserId): array
    {
        $users = [];
        $totalUsersRequired = 500;

        // Fetch initial users
        for ($i = $lastFetchedUserId + 1; $i <= $lastFetchedUserId + 10; $i++) {
            $response = Http::retry(3, 100)->get("https://jsonplaceholder.typicode.com/users/$i");
            Log::info('Fetched user', ['response' => $response->json()]);

            if ($response->successful()) {
                $userData = $response->json();
                $userData['name'] = $userData['name'] . '-' . uniqid(); // Make name unique
                $users[] = $userData;
            } else {
                break;
            }
        }

        // Ensure we have enough users, if not, duplicate
        while (count($users) < $totalUsersRequired-10) {
            foreach ($users as $userData) {
                if (count($users) >= $totalUsersRequired) {
                    break; // Stop if we have enough users
                }
                $duplicatedUserData = $userData;
                $duplicatedUserData['name'] = $userData['name'] . '-' . uniqid(); // Make name unique
                $duplicatedUserData['username'] = $userData['username'] . '-' . uniqid(); // Make username unique
                $duplicatedUserData['email'] = 'user' . uniqid() . '@example.com'; // Make email unique
                $users[] = $duplicatedUserData;
            }
        }
        Log::info('user array successfully created and duplicated');
        return $users;
    }

    private function insertUsers(array $users): void
    {
        $totalUsers = count($users);
        $posts = $this->fetchAndDuplicatePosts(10, $totalUsers * 10);
        $totalNumberOfComments = count($posts);
        $comments = $this->fetchAndDuplicateComments(10, $totalNumberOfComments * 10);

        foreach ($users as $index => $userData) {
            // Create the user
            $newUser = User::create([
                'name' => $userData['name'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'address' => json_encode($userData['address']),
                'phone' => $userData['phone'],
                'website' => $userData['website'],
                'company' => json_encode($userData['company']),
            ]);

            if (!$newUser) {
                Log::error('Failed to create user', ['userData' => $userData]);
                continue;
            }

            // Assign a unique set of 10 posts to this user
            $userPosts = array_slice($posts, $index * 10, 10);

            foreach ($userPosts as $postData) {
                // Create the post for the current user
                $post = $newUser->posts()->create([
                    'title' => $postData['title']. '-' . uniqid(),
                    'body' => $postData['body'],
                ]);

                // Assign a unique set of 10 comments to this post
                $postComments = array_slice($comments, $index * 10, 10);

                foreach ($postComments as $commentData) {
                    $post->comments()->create([
                        'author_name' => $commentData['name'],
                        'body' => $commentData['body'],
                    ]);
                }
            }

             BroadcastUserCountEvent::dispatch($index+1);
        }
    }


    private function fetchAndDuplicatePosts(int $batchSize, int $totalPostsNeeded): array
    {
            $posts = [];

            // Fetch the initial batch of posts
            for ($i = 1; $i <= $batchSize; $i++) {
                $response = Http::retry(3, 100)->get("https://jsonplaceholder.typicode.com/posts/$i");
                Log::info('Fetched post', ['response' => $response->json()]);

                if ($response->successful()) {
                    $posts[] = $response->json();
                }
            }

            // Duplicate the posts until we have the desired amount
            $initialPosts = $posts;
            while (count($posts) < $totalPostsNeeded) {
                foreach ($initialPosts as $postData) {
                    if (count($posts) >= $totalPostsNeeded) {
                        break;
                    }

                    $duplicatedPost = $postData;
                    $duplicatedPost['title'] = $postData['title'] . '-' . uniqid() .Str::random(5); // Make title unique
                    $posts[] = $duplicatedPost;
                }
            }

            Log::info('Post array successfully created and duplicated');
            return $posts;
    }

    private function fetchAndDuplicateComments(int $batchSize, int $totalCommentsNeeded): array
    {
        $comments = [];

        // Fetch the initial batch of comments
        for ($i = 1; $i <= $batchSize; $i++) {
            $response = Http::retry(3, 100)->get("https://jsonplaceholder.typicode.com/comments/$i");
            Log::info('Fetched comment', ['response' => $response->json()]);

            if ($response->successful()) {
                $comments[] = $response->json();
            }
        }

        // Duplicate the comments until we have the desired amount
        $initialComments = $comments;
        while (count($comments) < $totalCommentsNeeded) {
            foreach ($initialComments as $commentData) {
                if (count($comments) >= $totalCommentsNeeded) {
                    break;
                }

                $duplicatedComment = $commentData;
                $duplicatedComment['name'] = $commentData['name'] . '-' . uniqid(); // Make name unique
                $duplicatedComment['body'] = $commentData['body'] . '-' . uniqid(); // Make body unique
                $comments[] = $duplicatedComment;
            }
        }

        Log::info('comment array successfully created and duplicated');
        return $comments;
    }

}

