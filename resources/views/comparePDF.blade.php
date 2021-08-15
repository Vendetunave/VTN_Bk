<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>VendeTuNave</title>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body>
    <div>
        <?php
        $imagen = '';
        $imagenCss = '';
        $nombre = '';
        $precio = '';
        $ciudadLabel = '';
        $marcaLabel = '';
        $anio = '';
        $modeloLabel = '';
        $condicion = '';
        $tipoPrecioLabel = '';
        $diametro = '';
        $cilindraje = '';
        $kilometraje = '';
        $transmision = '';
        $blindaje = '';
        $placa = '';
        $descripcion = '';
        foreach ($vehiculo as $index => $producto) {
            $pathImage = '';

            if ($producto->new_image === 1) {
                $pathImage = 'https://vendetunave.s3.amazonaws.com/vendetunave/images/vehiculos/' . $producto->nameImage . ".webp";
            }

            if ($producto->new_image === 0) {
                $pathImage = 'https://vendetunave.s3.amazonaws.com/vendetunave/images/vehiculos/' . $producto->nameImage . "." . $producto->extension;
            }

            if ($producto->new_image === 2) {
                $pathImage = 'https://vendetunave.s3.amazonaws.com/vendetunave/images/thumbnails/' . $producto->nameImage . "300x300.webp";
            }

            $imagen .= '<td data-label="nombre" style="text-align: center">' .
                '<img style="max-height: 200px; object-fit: cover; max-width: 200px;" src="' . $pathImage . '">' .
                '</td>';

            $imagenCss .= '<td data-label="nombre" style="text-align: center;">' .
                '<img style="height: 0; object-fit: contain; width: 200px;" src="' . $pathImage . '">' .
                '</td>';

            $nombre .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Nombre</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->title .
                '</td>';
            $precio .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Precio</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                '$ ' . number_format($producto->precio, 0, '.', '.') . ' COP' .
                '</td>';
            $ciudadLabel .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Ubicación</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->ciudadLabel .
                '</td>';
            $marcaLabel .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Marca</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->marcaLabel .
                '</td>';
            $anio .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Año</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->ano .
                '</td>';
            $modeloLabel .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Modelo</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->modeloLabel .
                '</td>';
            $condicion .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Estado</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->condicion .
                '</td>';
            $tipoPrecioLabel .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Tipo de precio</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->tipoPrecioLabel .
                '</td>';
            $cilindraje .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Cilindraje</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->cilindraje, 0, '.', '.') . ' cc' .
                '</td>';
            $kilometraje .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Kilometraje</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->kilometraje, 0, '.', '.') . ' Km' .
                '</td>';
            $transmision .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Transmisión</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->transmisionLabel .
                '</td>';
            $blindaje .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Blindaje</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                (($producto->blindado) ? 'SI' : 'NO') .
                '</td>';
            $placa .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Último dígito Placa</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->placa .
                '</td>';
            $descripcion .=
                '<td data-label="nombre" style="vertical-align: top; text-align: justify; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Descripción</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->descripcion .
                '</td>';
        }

        ?>

        <table style="width: 100%; display: inline-table; border-collapse: separate;">
            <tbody>
                <tr>
                    <?= $imagen ?>
                </tr>
            </tbody>
        </table>
        <br />
        <br />
        <table style="width: 100%; border-collapse: separate;">
            <tbody>
                <tr>
                    <?= $imagenCss ?>
                </tr>
                <tr>
                    <?= $nombre ?>
                </tr>
                <tr>
                    <?= $precio ?>
                </tr>
                <tr>
                    <?= $ciudadLabel ?>
                </tr>

                <tr>
                    <?= $marcaLabel ?>
                </tr>
                <tr>
                    <?= $anio ?>
                </tr>
                <tr>
                    <?= $modeloLabel ?>
                </tr>
                <tr>
                    <?= $condicion ?>
                </tr>
                <tr>
                    <?= $tipoPrecioLabel ?>
                </tr>
                <tr>
                    <?= $cilindraje ?>
                </tr>
                <tr>
                    <?= $kilometraje ?>
                </tr>
                <tr>
                    <?= $transmision ?>
                </tr>
                <tr>
                    <?= $blindaje ?>
                </tr>
                <tr>
                    <?= $placa ?>
                </tr>
                <tr>
                    <?= $descripcion ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
