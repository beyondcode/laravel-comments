<?php

namespace BeyondCode\Comments\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Schema\Blueprint;
use BeyondCode\Comments\CommentsServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations(['--database' => 'sqlite']);
        $this->setUpDatabase();
        $this->createUser();
    }

    protected function getPackageProviders($app)
    {
        return [
            CommentsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:6Cu/ozj4gPtIjmXjr8EdVnGFNsdRqZfHfVjQkmTlg4Y=');
    }

    protected function setUpDatabase()
    {
        include_once __DIR__ . '/../database/migrations/create_comments_table.php.stub';

        (new \CreateCommentsTable())->up();

        $this->app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    protected function createUser()
    {
        User::forceCreate([
            'name' => 'User',
            'email' => 'user@email.com',
            'password' => 'test'
        ]);
    }

}