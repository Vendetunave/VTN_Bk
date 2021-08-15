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
        $power = '';
        $marcaLabel = '';
        $anio = '';
        $modeloLabel = '';
        $torque = '';
        $engine = '';
        $diametro = '';
        $comsbustible = '';
        $tipoCombustible = '';
        $trunk = '';
        $transmision = '';
        $traction = '';
        $autonomy = '';
        $performance = '';
        $security = '';
        $airbags = '';
        $wheels = '';
        $cushions = '';
        $weight = '';
        $descripcion = '';
        foreach ($vehiculo as $index => $producto) {
            $pathImage = '';

            if ($producto->new_image === 1) {
                $pathImage = 'https://vendetunave.s3.amazonaws.com/vendetunave/images/ficha-tecnica/' . $producto->nameImage . "." . $producto->extension;
            }

            if ($producto->new_image === 0) {
                $pathImage = 'https://vendetunave.s3.amazonaws.com/vendetunave/images/ficha-tecnica/' . $producto->nameImage . "." . $producto->extension;
            }

            if ($producto->new_image === 2) {
                $pathImage = 'https://vendetunave.s3.amazonaws.com/vendetunave/images/ficha-tecnica/' . $producto->nameImage . "." . $producto->extension;
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
                '$ ' . number_format($producto->price, 0, '.', '.') . ' COP' .
                '</td>';
            $power .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Potencia</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->power, 0, '.', '.') . ' HP' .
                '</td>';
            $marcaLabel .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Marca</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->marcaLabel .
                '</td>';
            $anio .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Año</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->year .
                '</td>';
            $modeloLabel .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Modelo</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->modeloLabel .
                '</td>';
            $torque .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Torque</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->torque, 0, '.', '.') . ' NM' .
                '</td>';
            $engine .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Motor</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->engine, 0, '.', '.') . ' C.C.' .
                '</td>';
            $comsbustible .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Tipo de motor</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->combustibleLabel .
                '</td>';
            $tipoCombustible .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Tipo de gasolina</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->fuel_type .
                '</td>';
            $trunk .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Capacidad del baúl</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->trunk . ' L' .
                '</td>';
            $transmision .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Transmisión</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->transmisionLabel .
                '</td>';
            $traction .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Tracción</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->traction .
                '</td>';
            $autonomy .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Autonomía</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->autonomy, 0, '.', '.') . ' Km' .
                '</td>';
            $performance .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Rendimiento</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->performance, 0, '.', '.') . ' Km por Galón' .
                '</td>';
            $security .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Seguridad en estrellas</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->security . ' Estrellas' .
                '</td>';
            $airbags .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Número de AirBags</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->airbags .
                '</td>';
            $wheels .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Rines</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->wheels .
                '</td>';
            $cushions .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Cojinería</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->cushions .
                '</td>';
            $weight .=
                '<td data-label="nombre" style="vertical-align: top; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Peso</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                number_format($producto->weight, 0, '.', '.') . ' K' .
                '</td>';
            $descripcion .=
                '<td data-label="nombre" style="vertical-align: top; text-align: justify; padding: 10px;">' .
                (($index === 0) ? '<h3 style="margin-bottom: 1px; margin-top: 0.5px">Descripción</h3>' : '<h3 style="margin-bottom: 1px; color: white; margin-top: 0.5px">.</h3>') .
                $producto->description .
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
                    <?= $power ?>
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
                    <?= $torque ?>
                </tr>
                <tr>
                    <?= $engine ?>
                </tr>
                <tr>
                    <?= $comsbustible ?>
                </tr>
                <tr>
                    <?= $tipoCombustible ?>
                </tr>
                <tr>
                    <?= $trunk ?>
                </tr>
                <tr>
                    <?= $transmision ?>
                </tr>
                <tr>
                    <?= $traction ?>
                </tr>
                <tr>
                    <?= $autonomy ?>
                </tr>
                <tr>
                    <?= $performance ?>
                </tr>
                <tr>
                    <?= $security ?>
                </tr>
                <tr>
                    <?= $airbags ?>
                </tr>
                <tr>
                    <?= $wheels ?>
                </tr>
                <tr>
                    <?= $cushions ?>
                </tr>
                <tr>
                    <?= $weight ?>
                </tr>
                <tr>
                    <?= $descripcion ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
