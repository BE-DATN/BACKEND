<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>form add Session</title>
</head>
<body>
    <form action="{{ asset('api/course/create-session') }}" method="get" enctype="multipart/form-data">
        <label for="">name</label><br>  
        <input type="text" name="name"><br>
        <label for="">course_id</label><br> 
        <input type="number" name="course_id" value="20"><br>
        <label for="">desc</label><br>  
        <input type="text" name="decription"><br>
        <label for="">thumbnail</label><br> 
        <input type="file" name="thumbnail"><br>
        <button type="submit">submit</button>
    </form>
</body>
</html>