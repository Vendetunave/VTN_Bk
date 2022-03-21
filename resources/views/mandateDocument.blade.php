<!doctype html>
<html>

<head>
    <style>
        @page {
            margin: 0cm 0cm;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            margin: 2.5cm 1cm 1cm 1cm;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.8cm;
            background-color: black;
            color: white;
            text-align: center;
            padding-top: 25px;
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

        main>p {
            text-align: justify;
            line-height: 23px;
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
            <p style="margin-top: 0.5em; font-weight: bold; letter-spacing: 1px;">CONTRATO DE MANDATO</p>
        </div>
    </header>

    <main>
        <br />
        <b>Ciudad y fecha</b>
        <p style="width: 100%; border-bottom: 1px solid black; margin: 0">&nbsp;&nbsp;{{ $information["ciudad"] }} {{ $information["fecha"] }}</p>
        <p style="line-height: 35px;">
            Contrato de mandato suscrito entre
            @if($information["nombre_mandatario"] && $information["nombre_mandatario"] !== "")
            <b>{{$information["nombre_mandatario"]}}</b>
            @else
            ________________________________________________
            @endif
            y
            @if($information["nombre_mandate"] && $information["nombre_mandate"] !== "")
            <b>{{$information["nombre_mandate"]}}</b>.
            @else
            ______________________________________________________________ .
            @endif
        </p>
        <p>
            Mayor de edad, vecino de esta ciudad, identificado con ________ No.
            @if($information["documento_mandate"] && $information["documento_mandate"] !== "")
            <b>{{$information["documento_mandate"]}}</b>
            @else
            ____________________________________
            @endif
            Quien para efectos del presente contrato se denominará <b>EL MANDANTE</b>, y de otro
            @if($information["nombre_mandatario"] && $information["nombre_mandatario"] !== "")
            <b>{{$information["nombre_mandatario"]}}</b>
            @else
            ____________________________________
            @endif
            también mayor de edad, vecino de esta ciudad, identificado con ________ No.
            @if($information["documento_mandatario"] && $information["documento_mandatario"] !== "")
            <b>{{$information["documento_mandatario"]}}</b>
            @else
            ____________________________________
            @endif
            Quien para efectos del presente contrato se denominará
            <b>EL MANDATARIO</b>, hemos acordado suscribir el siguiente contrato de mandato dando
            cumplimiento a la <b>Resolución 12379</b> expedida por el Ministerio de Transporte, el 28 de
            Diciembre de 2012 <b>( Art 5o )</b>, que se regirá por las normas civiles y comerciales que regulan
            la materia en concordancia con el <b>Art. 2149 G.C</b>. según las siguientes cláusulas:
        </p>

        <p style="margin: 0;">
            <b>PRIMERA: OBJETO DEL CONTRATO: EL MANDATARIO</b> por cuenta y riesgo del <b>MANDANTE</b> queda facultado para
            <b>solicitar, realizar, radicar, y retirar</b> el trámite de:
        </p>

        @if($information["tramite"] && $information["tramite"] !== "")
        <b>{{$information["tramite"]}}</b>
        @else
        <div style="height: 25px; width: 100%; border-bottom: 1px solid black"></div>
        <div style="height: 25px; width: 100%; border-bottom: 1px solid black"></div>
        <div style="height: 25px; width: 100%; border-bottom: 1px solid black"></div>
        @endif

        <p style="margin: 0; margin-top: 15px">Del vehículo de propiedad del <b>MANDANTE</b> identificado con las siguientes características:</p>
        <table style="width: 100%; border-collapse: separate; border-spacing: 15px;">
            <tr>
                <td>
                    <p class="cell-title">PLACA</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["placa"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">MARCA</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["marca"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">LÍNEA</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["ano"] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="cell-title">MODELO</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["modelo"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">CILINDRAJE</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["cilindraje"] }}
                    </p>
                </td>
                <td>
                    <p class="cell-title">MOTOR</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["motor"] }}
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <p class="cell-title">No. CHASIS</p>
                    <p class="cell-content">
                        &nbsp;&nbsp;&nbsp;{{ $information["chasis"] }}
                    </p>
                </td>
            </tr>
        </table>

        <p style="margin: 0;">
            Ante el <b>ORGANISMO DE TRANSITO Y TRANSPORTE</b> que corresponda, como consecuencia,
            <b>EL MANDATARIO</b> queda facultado para realizar todas las gestiones propias de este mandato
            y en especial para representar, notificarse, recibir, impugnar, transigir, desistir, sustituir,
            reasumir, pedir, conciliar o asumir obligación s en nombre del MANDANTE y
            quien SI<u>&nbsp;&nbsp;<b>X</b>&nbsp;&nbsp;</u> NO ___ <b>queda facultado para delegar el presente contrato de Mandato.</b>
        </p>

        <p style="text-align: center; margin: 0; margin-top: 14px">Página 1 de 2</p>
        <div class="page_break"></div>

        <br />
        <p>
            <b>SEGUNDA: OBLIGACIONES DEL MANDANTE: EL MANDANTE:</b> declara que la información
            contenida en los documentos que se anexan a la solicitud del trámite es veraz y auténtica,
            razón por la cual se hace responsable ante la autoridad competente de cualquier irregularidad que los mismos puedan contener.
        </p>

        <p>
            Este mandato se entiende conferido por término indefinido y solo perderá su eficacia
            cuando sea revocado expresamente o cuando se cumplan los objetivos en él previstos.
        </p>

        <p>Acepto,</p>

        <br />
        <br />
        <br />
        <br />
        <div>
            <div style="display: inline-block; width: 48%">
                <div style="display: inline-block; width: 65%">
                    <p style="width: 100%; border-bottom: 1px solid black; font-weight: bold; margin: 2px">&nbsp;&nbsp;X</p>
                    <b>MANDANTE</b>
                    <div>
                        <p style="display: inline-block;  width: 10%; margin-bottom: 0; margin-top: 10px;">C.C. </p>
                        <p style="display: inline-block; border-bottom: 1px solid black;  width: 80%; margin-left: 10px; margin-bottom: 0; margin-top: 0;">&nbsp;{{$information["documento_mandate"]}}</p>
                    </div>
                </div>
                <div style="display: inline-block; width: 30%; margin-left: 10px">
                    <div style="border: 1px solid black; height: 120px">

                    </div>
                    <p style="text-align: center">Huella</p>
                </div>
            </div>
            <div style="display: inline-block; width: 48%">
                <div style="display: inline-block; width: 65%">
                    <p style="width: 100%; border-bottom: 1px solid black; font-weight: bold; margin: 2px">&nbsp;&nbsp;X</p>
                    <b>MANDATARIO</b>
                    <div>
                        <p style="display: inline-block;  width: 10%; margin-bottom: 0; margin-top: 10px;">C.C. </p>
                        <p style="display: inline-block; border-bottom: 1px solid black;  width: 80%; margin-left: 10px; margin-bottom: 0; margin-top: 0;">&nbsp;{{$information["documento_mandatario"]}}</p>
                    </div>
                </div>
                <div style="display: inline-block; width: 30%; margin-left: 10px">
                    <div style="border: 1px solid black; height: 120px">

                    </div>
                    <p style="text-align: center">Huella</p>
                </div>
            </div>
        </div>

        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />
        <br />

        <p style="text-align: center; margin: 0; margin-top: 7px">Página 2 de 2</p>
    </main>
</body>

</html>