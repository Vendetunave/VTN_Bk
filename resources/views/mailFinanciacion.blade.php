<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
</head>
<body>
    <p>Hola! Alguien se ha interesado en el servicio de financiaci&oacute;n</p>
    <p>Estos son los datos del usuario que esta interesado:</p>
    <ul>
        <li>Nombre: {{ $nombre }}</li>
        <li>Apellido: {{ $apellido }}</li>
        <li>Fecha de nacimiento: {{ $fecha_nacimiento }}</li>
        <li>Correo electr&oacute;nico: {{ $email }}</li>
        <li>WhatsApp: {{ $whatsapp }}</li>
        <li>Precio del veh&iacute;culo: {{ $cuanto_cuesta }}</li>
        <li>Cuota inicial: {{ $cuota_inicial }}</li>
        <li>Número de cuotas: {{ $cuotas }}</li>
        <li>Reportado en Datacr&eacute;dito: {{ ($datacredito == 0)? 'NO': 'SI' }}</li>
        <li>Rango salar&iacute;al: {{ ((($salario == 1)? '1.600.000 - 2.500.000': ($salario == 2))? '2.500.000 - 5.000.000': '5.000.000 en adelante') }}</li>
    </ul>
</body>
</html>