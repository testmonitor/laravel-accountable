<?php

namespace TestMonitor\Accountable\Test;

use Illuminate\Database\Eloquent\SoftDeletes;
use TestMonitor\Accountable\Test\Models\Blog;
use TestMonitor\Accountable\Test\Models\Post;
use TestMonitor\Accountable\Test\Models\User;
use TestMonitor\Accountable\Traits\Accountable;
use TestMonitor\Accountable\AccountableSettings;

class TouchedRelationshipTest extends TestCase
{
    /**
     * @var \TestMonitor\Accountable\Test\Models\Blog
     */
    protected $blog;

    /**
     * @var \TestMonitor\Accountable\Test\Models\Post
     */
    protected $post;

    /**
     * @var AccountableSettings
     */
    protected $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabaseWithSoftDeletes();

        $this->blog = new class() extends Blog {
            use Accountable;
        };

        $this->post = new class() extends Post {
            use Accountable, SoftDeletes;
        };

        $this->config = app()->make(AccountableSettings::class);
    }

    /** @test */
    public function it_will_touches_the_parent_after_creating_a_new_child()
    {
        $firstUser = User::first();
        $this->actingAs($firstUser);

        $blog = new $this->blog();
        $blog->save();

        $secondUser = User::all()->last();
        $this->actingAs($secondUser);

        $post = new $this->post();
        $post->blog_id = $blog->id;
        $post->save();

        $this->assertEquals($blog->fresh()->created_by_user_id, $firstUser->id);
        $this->assertEquals($blog->fresh()->updated_by_user_id, $secondUser->id);

        $this->assertEquals($post->created_by_user_id, $secondUser->id);
        $this->assertEquals($post->updated_by_user_id, $secondUser->id);
    }

    /** @test */
    public function it_will_touches_the_parent_after_updating_a_child()
    {
        $firstUser = User::first();
        $this->actingAs($firstUser);

        $blog = new $this->blog();
        $blog->save();

        $post = new $this->post();
        $post->blog_id = $blog->id;
        $post->save();

        $secondUser = User::all()->last();
        $this->actingAs($secondUser);

        $post->name = 'modified';
        $post->save();

        $this->assertEquals($blog->fresh()->created_by_user_id, $firstUser->id);
        $this->assertEquals($blog->fresh()->updated_by_user_id, $secondUser->id);

        $this->assertEquals($post->created_by_user_id, $firstUser->id);
        $this->assertEquals($post->updated_by_user_id, $secondUser->id);
    }

    /** @test */
    public function it_will_touches_the_parent_after_deleting_a_child()
    {
        $firstUser = User::first();
        $this->actingAs($firstUser);

        $blog = new $this->blog();
        $blog->save();

        $post = new $this->post();
        $post->blog_id = $blog->id;
        $post->save();

        $secondUser = User::all()->last();
        $this->actingAs($secondUser);

        $post->delete();

        $this->assertEquals($blog->fresh()->created_by_user_id, $firstUser->id);
        $this->assertEquals($blog->fresh()->updated_by_user_id, $secondUser->id);

        $this->assertEquals($post->created_by_user_id, $firstUser->id);
        $this->assertEquals($post->updated_by_user_id, $secondUser->id);
        $this->assertEquals($post->deleted_by_user_id, $secondUser->id);
    }
}
