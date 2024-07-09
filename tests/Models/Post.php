<?php

namespace BeyondCode\Comments\Tests\Models;

use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasComments;

    protected $guarded = [];

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function (self $model) {
            $model->comments()->delete();
        });
    }
}
