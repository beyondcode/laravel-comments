<?php

namespace BeyondCode\Comments\Traits;

trait CanComment
{
    /**
     * Check if a comment for a specific model needs to be approved.
     *
     * @param  mixed  $model
     */
    public function needsCommentApproval($model): bool
    {
        return true;
    }
}
