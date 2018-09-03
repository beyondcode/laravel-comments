<?php

namespace BeyondCode\Comments\Tests\Models;

use BeyondCode\Comments\Traits\HasComments;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasComments;

    protected $guarded = [];
}