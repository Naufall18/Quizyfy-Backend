<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\User;

class ExamPolicy
{
    /**
     * Guru can only update their own exams.
     */
    public function update(User $user, Exam $exam): bool
    {
        return $user->id === $exam->created_by;
    }

    /**
     * Guru can only delete their own exams that are not currently in progress.
     */
    public function delete(User $user, Exam $exam): bool
    {
        return $user->id === $exam->created_by
            && $exam->status !== 'berlangsung';
    }

    /**
     * Guru can view any exam they created.
     */
    public function view(User $user, Exam $exam): bool
    {
        return $user->id === $exam->created_by || $user->role === 'admin';
    }
}
