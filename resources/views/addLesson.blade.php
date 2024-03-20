<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>form add Session</title>
</head>
<body>
    <form action="{{ asset('api/course/create-lesson') }}" method="get" enctype="multipart/form-data">
        <label for="">name</label><br>
        <input type="text" name="name"><br>
        <label for="">session id</label><br>
        <input type="number" name="session_id" value="16"><br>
        <label for="">name</label><br>
        <input type="text" name="name"><br>
        <label for="">video</label><br>
        <input type="file" name="video_url"><br>
        <button type="submit">submit</button>
    </form>
</body>
</html>