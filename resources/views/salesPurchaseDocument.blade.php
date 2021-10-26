<!doctype html>
<html>

<head>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 2cm 1cm 1cm;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: black;
            color: white;
            text-align: center;
            padding-top: 5px;
            padding-bottom: 15px;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            background-color: black;
            color: white;
            text-align: center;
        }

        footer>p {
            font-size: 23px;
            letter-spacing: 2px;
            line-height: 30px;
        }

        .table {
            display: block;
            margin-top: 30px;
        }

        .row {
            display: block;
            margin-top: 20px;
        }

        .cell {
            width: 44%;
            margin-left: 15px;
            margin-right: 15px;
            display: inline-block;
        }

        .cell-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .cell-content {
            border-bottom: 1px solid black;
            padding-bottom: 5px;
            font-size: 16px;
            margin-bottom: 0;
            margin-top: 10px;
        }

        tr>td {
            padding-bottom: 0.1em;
        }

        .page_break {
            page-break-before: always;
        }

        ul {
            list-style-position: outside;
        }
    </style>
</head>

<body>
    <header>
        <div style="display: inline;">
            <img alt="image logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZoAAAEQCAMAAAC+80LvAAAAAXNSR0IArs4c6QAAADNQTFRFR3BM////////////////////////////////////////////////////////////////z9GKYQAAABF0Uk5TADBg3+8Qn0C/gCDPUI+vcP9lpmI+AAAgAElEQVR4Xu1d2YKrIAxt3bXW+v9feytJIEDYrPZ2OnNepuOCyCErCJfLb8O17/tRoRsU7s+fj/6auu8PJ6Ht53EchnoNox7Gvk2V84fDsEnIEmXExm38E5/T0Vf3oUkxIaEep1TZf9iJth+XW4qAKJY+9Yw/lGKax32i4uJWpR71h2w8ZSWfldugfLT56a1dL+2TT//W4U9yjsBUdVkarB6WcexlP2xTgzY/3Z+/9hqetCQdsNszeJlzPOP+zstq5tT1fwhivsdpeSquqvccrv4+3B8hN6zv2P1/grML02MJUvLErXsErMUMFwSNSTsaxXb7C3NKcb2HjcttGedYiw504T10RWtk8U+pFWHuAq5Y89Rf6W7OZILrqyt4ePUwdKNm74k/NzoX17vMy61LsDLRaX6T4YabGBsPubw/WGgfgh7bZEWyG+2w1kYdjU+BUOT0/N6FTlduqWOltdooFP4HC7Ng929BVwvkgLq8IqTWvzSIO4/y7tLqY386LYpp9P3kpovosFZdUuN/oK+2y21q6LxX9vDUnVp1/nETRu+bgqaLO0+oovA/aOXtDpuaFVShc3BV1BhumrRz8UtReeqmvgvW5TpviTFqRSQT/wOfa7MaGNZQq4O1md0HgCbU3Nz+Yk8B7cPVZPVdd+JrN+A/Vy1X9dP8dxOFL3ipoWbE49TqylaNqwPk4kr/B2Og3wsemis0d6ZcJnVyO3B1LquJGtR64EFsDQwsDLrVlXggNTVMFxg6bVy05/aXiLbhEbPYBvmOzXy5eD5CD+eouxMh7BfScdtOg8iRT8BB8nQTzv1iVDYxjTc0jIQwxaPRP+AvNqmhpqNflI7ZCgUR01EOB3nsf16awWxLwuC3zYSnLpfH6qInpwuMBlywUQMsdBctENWFmyIPFN40f54A4srzWE8LI0WWaAhq0mz8Bi1IYGyAKEPNxgJEPoqlCDXajP15Agqt1dZLQJl0us0sHjd0Oors1KUSNfizvsSp0SL55wk8MTMj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" style="width: 100px; margin-left: -99px" />
        </div>
        <div style="display: inline-block; margin-left: 50px">
            <p style="margin-top: 1.5em; font-weight: bold; letter-spacing: 1px;">CONTRATO DE VENTA</p>
            <p style="margin: 0;">Esta Factura se asemeja en sus efectos a la</p>
            <p style="margin: 0;">letra de cambio (Articulo 774).</p>
        </div>
    </header>

    <footer>
        <p>vendetunave.co</p>
    </footer>

    <main>
        <br />
        <br />
        <div class="table">
            <div class="row">
                <div class="cell">
                    <p class="cell-title">Nombre del vendedor</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["nombre_vendedor"] }}
                    </p>
                </div>
                <div class="cell">
                    <p class="cell-title">Nombre del comprador</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["nombre_comprador"] }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="cell">
                    <p class="cell-title">Documento de Identidad</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["documento_vendedor"] }}
                    </p>
                </div>
                <div class="cell">
                    <p class="cell-title">Documento de Identidad</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["documento_comprador"] }}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="cell">
                    <p class="cell-title">Domicilio y Residencia</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["direccion_vendedor"] }}
                    </p>
                </div>
                <div class="cell">
                    <p class="cell-title">Domicilio y Residencia</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["direccion_comprador"] }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="cell">
                    <p class="cell-title">Teléfonos</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["tel_vendedor"] }}
                    </p>
                </div>
                <div class="cell">
                    <p class="cell-title">Teléfonos</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["tel_comprador"] }}
                    </p>
                </div>
            </div>
        </div>
        <p style="text-align: justify; margin-bottom: 50px; margin-top: 30px">
            La mercancía a que se refiere la presente factura cambiaria de compraventa, corresponde a
            una venta efectiva, la cual ha sido entregada real y materialmente al comprador, quien la
            recibe a entera satisfacción, al momento de la expedición de este documento.
        </p>

        <h3>Características de la Mercancía</h3>
        <table style="width: 100%; border-collapse: separate; border-spacing: 15px;">
            <tr>
                <td>
                    <p class="cell-title">Clase de vehículo</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["clase_vehiculo"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">Marca</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["marca"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">Modelo</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["modelo"] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p class="cell-title">Tipo de Carrocería</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["tipo_carroceria"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">Color</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["color"] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="cell-title">N° Motor</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["numero_motor"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">N° Chasis</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["numero_chasis"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">N° serie</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["numero_serie"] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="cell-title">N° de Puertas</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["numero_puertas"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">Capacidad</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["capacidad"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">Servicio</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["servicio"] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="cell-title">Placa N°</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["placa"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">Precio</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;$ {{ $information["precio"] }}
                    </p>
                </td>
            </tr>
        </table>

        <br />
        <p style="text-align: center; margin: 0">Página 1 de 2</p>
        <div class="page_break"></div>

        <br />
        <h3>CONDICIONES DE PAGO:</h3>
        <p style="text-align: justify">
            La mercancía a que se refiere la presente factura cambiaria de compraventa, corresponde a
            una venta efectiva, la cual ha sido entregada real y materialmente al comprador, quien la
            recibe a entera satisfacción, al momento de la expedición de este documento.
        </p>

        <ul>
            <li style="margin-bottom: 10px">
                El VENDEDOR garantiza que el vehículo lo ha tenido en posesión material desde que lo adquirió y
                está libre de todos los gravámenes, derechos de usufructo, limitaciones y condiciones de dominio,
                embargos, litigios pendientes, prendas etc., que fue introducido legalmente al país y que en caso de
                vicios redhibitorios (Art. 1915 C.C.C.) saldrá en defensa del COMPRADOR siempre y cuando haya
                existido al tiempo de la venta y que el COMPRADOR no hubiese tenido conocimiento de ellos al
                momento de la transacción.
            </li>
            <li style="margin-bottom: 10px">
                En el evento de pago sujeto a plazo, el vendedor se reserva el dominio de la cosa vendida hasta que
                el COMPRADOR haya pagado la totalidad del precio. Quien solo adquirirá la propiedad de la cosa
                con el pago de la última cuota.
            </li>
            <li style="margin-bottom: 10px">
                El COMPRADOR no podrá enajenar, ni transferir el vehículo a quien se refiere la presente letra cam-
                biaria, so pena de incurrir en el delito de abuso de confianza contemplado en el Código Penal
                Colombiano, hasta tanto no pague la totalidad del precio pactado.
            </li>
            <li style="margin-bottom: 10px">
                Por tratarse de vehículos usados, el VENDEDOR no da ninguna garantía en lo que concierne a las
                condiciones mecánicas de los mismos.
            </li>
            <li style="margin-bottom: 10px">
                Este contrato se expide exclusivamente para realizar el traspaso de propiedad, según Resolución No
                003275 del 12 Agosto de 2.008 del Ministerio de Transporte.
            </li>
        </ul>

        <br />
        @if($information["clausulas"] && $information["clausulas"] !== "")
        <p style="display: inline;">
        <p style="display: inline; font-weight: bold;">Clausulas adicionales: </p>
        <p style="display: inline;">
            {{ $information["clausulas"] }}
        </p>
        </p>
        <br />
        <br />
        <br />
        @else
        <p style="display: inline; margin: 0">
        <p style="display: inline; font-weight: bold;">Clausulas adicionales: </p>
        <p style="display: inline; border-bottom: 1px solid black">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>
        <br />
        <br />
        <br />
        <div style="border-bottom: 1px solid black"></div>
        <br />
        <br />
        </p>
        @endif

        <div>
            <div style="display: inline-block">
                <p>ACEPTA, de conformidad con todo lo estipulado, se firma hoy </p>
                <br />
                <br />
            </div>
            <div style="display: inline-block; margin-left: 15px">
                <table style="border-collapse: separate; border-spacing: 10px;">
                    <tr>
                        <td>_______</td>
                        <td>_______</td>
                        <td>_______</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">Día</td>
                        <td style="text-align: center;">Mes</td>
                        <td style="text-align: center;">Año</td>
                    </tr>
                </table>
            </div>
        </div>

        <br />
        <br />
        <br />
        <br />

        <div>
            <div style="display: inline-block; width: 48%">
                <div style="display: inline-block; width: 65%">
                    <p style="width: 100%; border-bottom: 1px solid black; font-weight: bold; margin: 2px">&nbsp;&nbsp;X</p>
                    <b>VENDEDOR</b>
                    <div>
                        <p style="display: inline-block;  width: 10%; margin-bottom: 0; margin-top: 10px;">C.C. </p>
                        <p style="display: inline-block; border-bottom: 1px solid black;  width: 80%; margin-left: 10px; margin-bottom: 0; margin-top: 0;">&nbsp;{{ $information["documento_vendedor"] }}</p>
                    </div>
                </div>
                <div style="display: inline-block; width: 30%; margin-left: 10px">
                    <div style="border: 1px solid black; height: 120px">

                    </div>
                    <p style="text-align: center">Huella</p>
                </div>
            </div>
            <div style="display: inline-block; width: 48%; margin-left: 15px">
                <div style="display: inline-block; width: 65%">
                    <p style="width: 100%; border-bottom: 1px solid black; font-weight: bold; margin: 2px">&nbsp;&nbsp;X</p>
                    <b>COMPRADOR</b>
                    <div>
                        <p style="display: inline-block;  width: 10%;  margin-bottom: 0; margin-top: 10px;">C.C. </p>
                        <p style="display: inline-block; border-bottom: 1px solid black;  width: 80%; margin-left: 10px;  margin-bottom: 0; margin-top: 0;">&nbsp;{{ $information["documento_comprador"] }}</p>
                    </div>
                </div>
                <div style="display: inline-block; width: 30%; margin-left: 10px">
                    <div style="border: 1px solid black; height: 120px">

                    </div>
                    <p style="text-align: center">Huella</p>
                </div>
            </div>
        </div>
        <p style="text-align: center; margin: 0; margin-top: -30px">Página 2 de 2</p>

    </main>
</body>

</html>