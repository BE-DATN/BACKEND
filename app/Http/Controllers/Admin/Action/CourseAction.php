<?php

namespace App\Http\Controllers\Admin\Action;

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
         * &course_name=
         * &course_id=
         * &created_at=
         * &sort=['view','esc']
         */

        $query = Course::select(
            [
                'courses.id',
                'courses.name',
                'users.username',
                'courses.description',
                'courses.thumbnail',
                'courses.video_demo_url',
                'price',
                'views',
                'courses.status',
                'courses.created_at',
                'courses.updated_at'
            ]
        )
            ->join('users', 'users.id', '=', 'courses.created_by')
            ->where('courses.status', '1');
        // Search dữ liệu user ngoài bảng course -> join user

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
                'courses.created_by',
                '=',
                array_get($attribute, 'author_id')
            );
        }

        // search course_name
        if (array_get($attribute, 'course_name')) {
            $courseName = array_get($attribute, 'course_name');
            $query->where(
                'courses.name',
                'like',
                "%{$courseName}%"
            );
        }

        // search course_id
        if (array_get($attribute, 'course_id')) {
            $query->where(
                'courses.id',
                '=',
                array_get($attribute, 'course_id')
            );
        }

        // Search time 
        // sắp xếp view||like asc||desc
        if (array_get($attribute, 'sort')) {
            $column = array_get($attribute, 'sort')[0];
            $valueSort = array_get($attribute, 'sort')[1];
            // dd($valueSort);
            $query->orderBy($column, $valueSort);
        }
        return $query->paginate(Course::limit);
        // return $query;
    }
}
