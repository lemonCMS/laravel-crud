<?php

namespace TestApp\Models\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use TestApp\Models\Blog;
use TestApp\Models\User;

class BlogPolicy
{
    use HandlesAuthorization;

    public function default(?User $user)
    {
        return true;
    }

    public function view(?User $user, Blog $entity)
    {
        return true;
    }

    public function create(?User $user)
    {
        return true;
    }

    public function update(?User $user, Blog $account)
    {
        return true;
    }

    public function delete(?User $user, Blog $account)
    {
        return true;
    }

    public function restore(?User $user, Blog $account)
    {
        return true;
    }

    public function forceDelete(?User $user, Blog $account)
    {
        return true;
    }
}
