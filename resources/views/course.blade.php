<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add to cart</title>
</head>

<body>
    <h3>add cart</h3>
    <div style="display: flex; width: 600px; justify-content: space-between;">
        @foreach ($courses as $course)
        <a href="/api/cart/add-item/{{ $course->id }}" style="width: 100px; height: 150px; border-radius: 4px; border: 1px solid purple; padding: 2px; display: block;">
            <img src="thumbnail_65f2bd6b10662_.jpg" style="width: 100%; object-fit: cover;" alt="">
            {{ $course->name }}
        </a>    
        @endforeach
    </div>
</body>

</html>