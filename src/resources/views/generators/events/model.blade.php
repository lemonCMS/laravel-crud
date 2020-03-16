namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LemonCMS\LaravelCrud\Model\CrudTrait;

class {{$model}} extends Model
{
    use CrudTrait;

    /**
     * $include holds the relationships that may be
     * accessed with the query param include e.g.
     *
     * ['tags', 'user', 'user.tags']
     *
     * http://localhost/api/blogs?include=tags,user,user.avatar
     *
     * @var array
     */
    protected $includes = [
    ];

    /**
     * Array of column names that can be used to order by.
     *
     * @var array
     */
    protected $orderFields = [
        'id', 'name', 'created_at', 'modified_at',
    ];

    /**
     * Array of column names that can be used to search in.
     * Only defining column names will fallback in to full match.
     *
     * [id, tag]
     *
     * Alternatively provide an callback function
     *
     * ['name' => function (Builder $query, $value) {
     *      return $query->where('name', 'like', "%{$value}%")
     * }]
     *
     * @return array
     */
    protected function search()
    {
        return ['id'];
    }
}
