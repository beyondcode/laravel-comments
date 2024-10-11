<?php

namespace BeyondCode\Comments\Tests;

use BeyondCode\Comments\Events\CommentAdded;
use BeyondCode\Comments\Events\CommentDeleted;
use BeyondCode\Comments\Tests\Models\ApprovedUser;
use BeyondCode\Comments\Tests\Models\Post;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;

class CommentsTest extends TestCase
{
    /** @test */
    public function users_without_commentator_interface_do_not_get_approved()
    {
        $post = Post::create([
            'title' => 'Some post',
        ]);

        $post->comment('this is a comment');

        $comment = $post->comments()->first();

        $this->assertFalse($comment->is_approved);
    }

    /** @test */
    public function models_can_store_comments()
    {
        $post = Post::create([
            'title' => 'Some post',
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
            'title' => 'Some post',
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
            'title' => 'Some post',
        ]);

        $comment = $post->comment('this is a comment');

        $this->assertSame($user->toArray(), $comment->commentator->toArray());
    }

    /** @test */
    public function comments_can_be_posted_as_different_users()
    {
        $user = User::first();

        $post = Post::create([
            'title' => 'Some post',
        ]);

        $comment = $post->commentAsUser($user, 'this is a comment');

        $this->assertSame($user->toArray(), $comment->commentator->toArray());
    }

    /** @test */
    public function comments_can_be_approved()
    {
        $user = User::first();

        $post = Post::create([
            'title' => 'Some post',
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
            'title' => 'Some post',
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
            'title' => 'Some post',
        ]);

        $comment = $post->commentAsUser($user, 'this is a comment');

        $this->assertTrue($comment->is_approved);
    }

    /** @test */
    public function comments_have_an_approved_scope()
    {
        $user = ApprovedUser::first();

        $post = Post::create([
            'title' => 'Some post',
        ]);

        $post->comment('this comment is not approved');
        $post->commentAsUser($user, 'this comment is approved');

        $this->assertCount(2, $post->comments);
        $this->assertCount(1, $post->comments()->approved()->get());

        $this->assertSame('this comment is approved', $post->comments()->approved()->first()->comment);
    }

    /** @test */
    public function comments_are_deleted_when_posts_are_deleted()
    {
        $post = Post::create([
            'title' => 'Some post',
        ]);

        $comment = $post->comment('this comment will be deleted');

        $post->delete();

        $this->assertFalse($comment->exists());
    }

    /** @test */
    public function replies_are_deleted_when_post_comments_are_deleted()
    {
        config(['comments.delete_replies_along_comments' => true]);

        $post = Post::create([
            'title' => 'Some post',
        ]);

        $comment = $post->comment('this comment will be deleted');
        $reply = $comment->comment('this comment will be deleted too');

        $comment->delete();

        $this->assertFalse($reply->exists());
    }

    /** @test */
    public function comment_added_event_is_dispatched_when_comment_is_created()
    {
        Event::fake([CommentAdded::class]);

        $post = Post::create([
            'title' => 'Some post',
        ]);

        $comment = $post->comment('this comment is added');

        Event::assertDispatched(CommentAdded::class, function ($event) use ($comment) {
            return $event->comment->is($comment);
        });
    }

    /** @test */
    public function comment_deleted_event_is_dispatched_when_comment_is_deleted()
    {
        Event::fake([CommentDeleted::class]);

        $post = Post::create([
            'title' => 'Some post',
        ]);

        $comment = $post->comment('this comment is added');

        $comment->delete();

        Event::assertDispatched(CommentDeleted::class, function ($event) use ($comment) {
            return $event->comment->is($comment);
        });
    }
}
