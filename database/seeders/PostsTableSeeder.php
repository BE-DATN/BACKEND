<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $title = [];
        $content = [];
        $rand = [
            "Bài: Giới thiệu về lập trình dữ liệu**
            Lập trình dữ liệu là một lĩnh vực khoa học máy tính liên quan đến việc thu thập, lưu trữ, xử lý và phân tích dữ liệu. Khóa học này sẽ giới thiệu cho bạn các khái niệm cơ bản về lập trình dữ liệu, bao gồm:
            * Các loại dữ liệu
            * Cấu trúc dữ liệu
            * Thuật toán
            * Công cụ lập trình",

            "Bài: Python cho lập trình dữ liệu**
                Python là một ngôn ngữ lập trình phổ biến cho lập trình dữ liệu. Khóa học này sẽ hướng dẫn bạn cách sử dụng Python để:
                * Đọc và ghi dữ liệu
                * Xử lý dữ liệu
                * Phân tích dữ liệu
                * Tạo trực quan dữ liệu
            ",

            "Bài: R cho lập trình dữ liệu**
            R là một ngôn ngữ lập trình khác được sử dụng rộng rãi cho lập trình dữ liệu. Khóa học này sẽ hướng dẫn bạn cách sử dụng R để:
            * Phân tích thống kê
            * Học máy
            * Khai phá dữ liệu
            * Tạo trực quan dữ liệu",

            "Bài: Cấu trúc dữ liệu cơ bản**
            Cấu trúc dữ liệu là cách tổ chức dữ liệu trong bộ nhớ máy tính. Khóa học này sẽ giới thiệu cho bạn các cấu trúc dữ liệu cơ bản, bao gồm:
            * Mảng
            * Danh sách liên kết
            * Ngan xếp
            * Hàng đợi"
        ];
        for ($i = 1; $i <= 20; $i++) {
            $title[$i - 1] = "Tiêu đề bài viết $i";
            $content[$i - 1] = "Nội dung bài viết $i <br>" . $rand[rand(0, 3)];
        }
        for ($i = 1; $i <= 20; $i++) {
            DB::table('posts')->insert([
                'created_by' => rand(1, 10), 
                'title' => $title[$i - 1],
                'content' => $content[$i - 1],
                'thumbnail' => 'path/to/thumbnail' . $i . '.jpg',
                'likes' => rand(1, 100),
                'views' => rand(50, 1000),
                'status' => rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
