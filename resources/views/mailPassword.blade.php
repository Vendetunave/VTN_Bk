<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
</head>

<body>
    <h3>Restablecimiento de su contraseña</h3>
    <p style="font-weight: bold">Estimado(a) {{ $user->nombre }},</p>
    <p>Hemos recibido una solicitud para restablecer la contraseña asociada a su cuenta de VendeTuNave
        {{ $user->email }}. </p>
    <p>
        Para restablecer su contraseña haga clic en el siguiente link de seguridad o copie y péguelo en su navegador:
        https://vendetunave.co/restablecer/link/{{ $token }}
    </p>
</body>

</html>
