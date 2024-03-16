<?php

namespace App\Http\Controllers\Course\Action;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseAction
{
    protected $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function search()
    {
        $inp = $this->request->all();
        $attribute = removeNullOrEmptyString($inp);
        /** key search
         * ?author_name=
         * &author_id=
         * &post_name=
         * &post_id=
         * &created_at=
         * &sort=['view','esc']
         */
        $query = DB::table('posts')->select('*');
        // Search dữ liệu user ngoài bảng post -> join user
        if (array_get($attribute, 'author_name') || array_get($attribute, 'author_id')) {
            $query->join('users', 'users.id', '=', 'posts.created_by');

            // search author_name users.username
            if (array_get($attribute, 'author_name')) {
                $authorName = array_get($attribute, 'author_name');
                $query->where(
                    'users.username',
                    'like',
                    "%{$authorName}%"
                );
            }
            // search user_id người tạo
            if (array_get($attribute, 'author_id')) {
                $query->where(
                    'posts.created_by',
                    '=',
                    array_get($attribute, 'author_id')
                );
            }
        }
        // search post_name
        if (array_get($attribute, 'post_name')) {
            $postName = array_get($attribute, 'post_name');
            $query->where(
                'posts.title',
                'like',
                "%{$postName}%"
            );
        }
        // search post_id
        if (array_get($attribute, 'post_id')) {
            $query->where(
                'posts.id',
                '=',
                array_get($attribute, 'post_id')
            );
        }
        // Search time 
        // sắp xếp view||like asc||desc
        if (array_get($attribute, 'sort')) {
            $column = array_get($attribute, 'sort')[0];
            $valueSort = array_get($attribute, 'sort')[1];
            dd($valueSort);
            $query->orderBy($column, $valueSort);
        }

        // return $query->paginate(Post::Limit);
        return $query->get();
    }
}
