<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>form</title>
</head>
<body>
    <form action="/api/post/create" method="post" enctype="multipart/form-data">
        @csrf
        <input type="text" name="title"><br><br>
        <input type="file" name="thumbnail"><br><br>
        <label for=""><input type="radio" name="status" value="0">Ẩn</label>
        <label for=""><input type="radio" name="status" value="1">Hiện</label><br><br>
        <textarea name="content" id="" cols="20" rows="5"></textarea><br>
        <button type="submit">create</button>
    </form>
</body>
</html>