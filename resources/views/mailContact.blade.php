<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
</head>

<body>
    <p>Hola! Alguien se ha interesado en tu publicaci&oacute;n 
        <a href="https://vendetunave.co/vehiculos/detalle/{{ $id }}">
            Ver aquí
        </a>
    </p>
    <p>Estos son los datos del usuario que esta interesado:</p>
    <ul>
        <li>Teléfono: {{ $telContactoForm }}</li>
        <li>Mensaje: {{ $msjContactoForm }}</li>
    </ul>
</body>

</html>