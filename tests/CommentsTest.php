<?php

namespace BeyondCode\Comments\Tests;

use BeyondCode\Comments\Tests\Models\ApprovedUser;
use BeyondCode\Comments\Tests\Models\Post;
use Illuminate\Foundation\Auth\User;

class CommentsTest extends TestCase
{
    /** @test */
    public function users_without_commentator_interface_do_not_get_approved()
    {
        $post = Post::create([
            'title' => 'Some post'
        ]);

        $post->comment('this is a comment');

        $comment = $post->comments()->first();

        $this->assertFalse($comment->is_approved);
    }

    /** @test */
    public function models_can_store_comments()
    {
        $post = Post::create([
            'title' => 'Some post'
        ]);

        $post->comment('this is a comment');
        $post->comment('this is a different comment');

        $this->assertCount(2, $post->comments);

        $this->assertSame('this is a comment', $post->comments[0]->comment);
        $this->assertSame('this is a different comment', $post->comments[1]->comment);
    }

    /** @test */
    public function comments_without_users_have_no_relation()
    {
        $post = Post::create([
            'title' => 'Some post'
        ]);

        $comment = $post->comment('this is a comment');

        $this->assertNull($comment->commentator);
        $this->assertNull($comment->user_id);
    }

    /** @test */
    public function comments_can_be_posted_as_authenticated_users()
    {
        $user = User::first();

        auth()->login($user);

        $post = Post::create([
            'title' => 'Some post'
        ]);

        $comment = $post->comment('this is a comment');

        $this->assertSame($user->toArray(), $comment->commentator->toArray());
    }

    /** @test */
    public function comments_can_be_posted_as_different_users()
    {
        $user = User::first();

        $post = Post::create([
            'title' => 'Some post'
        ]);

        $comment = $post->commentAsUser($user, 'this is a comment');

        $this->assertSame($user->toArray(), $comment->commentator->toArray());
    }

    /** @test */
    public function comments_can_be_approved()
    {
        $user = User::first();

        $post = Post::create([
            'title' => 'Some post'
        ]);

        $comment = $post->comment('this is a comment');

        $this->assertFalse($comment->is_approved);

        $comment->approve();

        $this->assertTrue($comment->is_approved);
    }

    /** @test */
    public function comments_resolve_the_commented_model()
    {
        $user = User::first();

        $post = Post::create([
            'title' => 'Some post'
        ]);

        $comment = $post->comment('this is a comment');

        $this->assertSame($comment->commentable->id, $post->id);
        $this->assertSame($comment->commentable->title, $post->title);
    }

    /** @test */
    public function users_can_be_auto_approved()
    {
        $user = ApprovedUser::first();

        $post = Post::create([
            'title' => 'Some post'
        ]);

        $comment = $post->commentAsUser($user, 'this is a comment');

        $this->assertTrue($comment->is_approved);
    }

    /** @test */
    public function comments_have_an_approved_scope()
    {
        $user = ApprovedUser::first();

        $post = Post::create([
            'title' => 'Some post'
        ]);

        $post->comment('this comment is not approved');
        $post->commentAsUser($user, 'this comment is approved');

        $this->assertCount(2, $post->comments);
        $this->assertCount(1, $post->comments()->approved()->get());

        $this->assertSame('this comment is approved', $post->comments()->approved()->first()->comment);
    }
}
