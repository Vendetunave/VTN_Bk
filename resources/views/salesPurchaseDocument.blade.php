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
        <div>
            <p style="margin-top: 0.5em; font-weight: bold; letter-spacing: 1px; margin-bottom: 0;">CONTRATO DE VENTA</p>
            <p style="margin: 0;">Esta Factura se asemeja en sus efectos a la</p>
            <p style="margin: 0;">letra de cambio (Articulo 774).</p>
        </div>
    </header>

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