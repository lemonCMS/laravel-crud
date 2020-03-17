
namespace App\Models\Policies;

use App\Models\{{$model}};
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class {{$policy}}
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any accounts.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function default(?User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view.
     *
     * @param  \App\User  $user
     * @param  \App\Models\{{$model}}  $model
     * @return mixed
     */
    public function view(?User $user, {{$model}} $model)
    {
        // todo
        // if ($model->users()->where('id', $user->id)->first()) {
        //     return true;
        // }
        //
        // return true;
    }

    /**
     * Determine whether the user can create.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(?User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update.
     *
     * @param  \App\User  $user
     * @param  \App\Models\{{$model}}  $model
     * @return mixed
     */
    public function update(?User $user, {{$model}} $model)
    {
        return false;
    }

    /**
     * Determine whether the user can delete .
     *
     * @param  \App\User  $user
     * @param  \App\Models\{{$model}}  $model
     * @return mixed
     */
    public function delete(?User $user, {{$model}} $model)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the account.
     *
     * @param  \App\User $user
     * @param  \App\Models\{{$model}}  $model
     * @return mixed
     */
    public function restore(?User $user, {{$model}}  $model)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete.
     *
     * @param  \App\User  $user
     * @param  \App\Models\{{$model}}  $model
     * @return mixed
     */
    public function forceDelete(?User $user, {{$model}}  $model)
    {
        return true;
    }
}
