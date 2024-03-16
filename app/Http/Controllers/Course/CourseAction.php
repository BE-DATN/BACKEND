<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseAction extends Controller
{
        public function search(Request $request)
        {
           
            $input = $request->all();
            $attributes = $this->removeNullOrEmptyString($input);
    
            // lấy danh sách khóa học
            $query = DB::table('courses')
                        ->select(['id', 'name', 'description', 'duration', 'price', 'views', 'status','thumbnail','video_demo_url','timestamp']);
    
            // tìm theo tên
            if (isset($attributes['name'])) {
                $query->where('name', 'like', '%' . $attributes['name'] . '%');
            }
    
            // sắp xếp
            if (isset($attributes['sort'])) {
                $sort = explode(',', $attributes['sort']);
                $query->orderBy($sort[0], $sort[1]);
            }
    
            // phân trang
            $courses = $query->paginate(10);
    
            // Trả
            return response()->json($courses);
    
            // return view('courses.index', compact('courses'));
        }
        private function removeNullOrEmptyString(array $array)
        {
            return array_filter($array, function ($value) {
                return $value !== null && $value !== '';
            });
        }
}
