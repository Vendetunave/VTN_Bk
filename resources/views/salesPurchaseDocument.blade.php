<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
</head>

<body>
    @foreach($information as $key => $item)
    <h3>{{ $key }}</h3>
    <p>{{ $item }}</p>
    <br />
    @endforeach
</body>

</html>