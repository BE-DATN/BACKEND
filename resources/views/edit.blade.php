<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>form</title>
</head>
<body>
    <form action="/api/post/edit/22" method="post" enctype="multipart/form-data">
        @csrf
        <input type="text" name="title" value="{{$title}}"><br><br>
        <img src="{{$thumbnail}}" width="100" style="object-fit: cover;" alt="">
        <input type="file" name="thumbnail"><br><br>
        <label for=""><input type="radio" name="status" value="0" {{ $status == 0 ? 'checked':'' }}>Ẩn</label>
        <label for=""><input type="radio" name="status" value="1" {{ $status == 1 ? 'checked':'' }}>Hiện</label><br><br>
        <textarea name="content" id="" cols="20" rows="5" >{{$content}}</textarea><br>
        <button type="submit">create</button>
    </form>
</body>
</html>