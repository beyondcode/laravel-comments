# Add comments to your Laravel application

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beyondcode/laravel-comments.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-comments)
[![Build Status](https://img.shields.io/travis/beyondcode/laravel-comments/master.svg?style=flat-square)](https://travis-ci.org/beyondcode/laravel-comments)
[![Quality Score](https://img.shields.io/scrutinizer/g/beyondcode/laravel-comments.svg?style=flat-square)](https://scrutinizer-ci.com/g/beyondcode/laravel-comments)
[![Total Downloads](https://img.shields.io/packagist/dt/beyondcode/laravel-comments.svg?style=flat-square)](https://packagist.org/packages/beyondcode/laravel-comments)

Add the ability to associate comments to your Laravel Eloquent models. The comments can be approved and nested.

```php
$post = Post::find(1);

$post->comment('This is a comment');

$post->commentAsUser($user, 'This is a comment from someone else');
```

## Installation

You can install the package via composer:

```bash
composer require beyondcode/laravel-comments
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="BeyondCode\Comments\CommentsServiceProvider" --tag="migrations"
```

After the migration has been published you can create the media-table by running the migrations:

```bash
php artisan migrate
```

You can publish the config-file with:

```bash
php artisan vendor:publish --provider="BeyondCode\Comments\CommentsServiceProvider" --tag="config"
```

## Usage

### Registering Models

To let your models be able to receive comments, add the `HasComments` trait to the model classes.

``` php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use BeyondCode\Comments\Traits\HasComments;

class Post extends Model
{
    use HasComments;
    ...
}
```

### Creating Comments

To create a comment on your commentable models, you can use the `comment` method. It receives the string of the comment that you want to store.

```php
$post = Post::find(1);

$comment = $post->comment('This is a comment from a user.');
```

The comment method returns the newly created comment class.

Sometimes you also might want to create comments on behalf of other users. You can do this using the `commentAsUser` method and pass in your user model that should get associated
with this comment:

```php
$post = Post::find(1);

$comment = $post->commentAsUser($yourUser, 'This is a comment from someone else.');
```

### Approving Comments

By default, all comments that you create are not approved - this is just a boolean flag called `is_approved` that you can use in your views/controllers to filter out comments that you might not yet want to display.

To approve a single comment, you may use the `approve` method on the Comment model like this:

```php
$post = Post::find(1);
$comment = $post->comments->first();

$comment->approve();
```

### Auto Approve Comments

If you want to automatically approve a comment for a specific user (and optionally model) you can let your User model implement the following interface and method:

```php
namespace App\Models;

use BeyondCode\Comments\Contracts\Commentator;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Commentator
{
    /**
     * Check if a comment for a specific model needs to be approved.
     * @param mixed $model
     * @return bool
     */
    public function needsCommentApproval($model): bool
    {
        return false;    
    }
    
}
```

The `needsCommentApproval` method received the model instance that you want to add a comment to and you can either return `true` to mark the comment as **not** approved, or return `false` to mark the comment as **approved**.

### Retrieving Comments

The models that use the `HasComments` trait have access to it's comments using the `comments` relation:

```php

$post = Post::find(1);

// Retrieve all comments
$comments = $post->comments;

// Retrieve only approved comments
$approved = $post->comments()->approved()->get();

```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email marcel@beyondco.de instead of using the issue tracker.

## Credits

- [Marcel Pociot](https://github.com/mpociot)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
