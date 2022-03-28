<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        :root {
    --red: #ff2424;
    --red-shadow: #b60606;
}

        body {
            font-family: Arial, Helvetica, sans-serif
        }

        .button {
            background: linear-gradient(to bottom, #f82f2f, #ca0808);
            padding: 12px;
            color: white !important;
            text-decoration: none;
            font-family: 'Franklin Gothic', 'Arial Narrow', Arial, sans-serif;
            /* border-radius:5px; */
            font-size: 20px;
            /* line-height: 1.5; */
            display: inline-block;
        }

        .button:hover {
            background: linear-gradient(to bottom, #f83f3f, #e90c0c);
        }

        .text-dark{
            color: rgb(92, 92, 92);
        }

        .text-primary{
            color:#ff3a3a;
            /* text-shadow: 1px 1px grey; */
        }
        .text-red-l{
            color:rgb(255, 122, 122);
        }
        p{
            font-size: 14px;
        }
        .text-warning{
            color: orange;
        }
    </style>
</head>

<body>
    <br>
    <br>
    <div class="container2">

        <div class="card"
            style="">
            <div style="text-align:center;">

            <img src="{{ $message->embed(public_path() . '/images/logotipos/klikler-email.jpg') }}" />
                <br>
                <h3 class="text-primary" >Resultado solicitud activación plan:  <span style="color: rgb(34, 34, 34)">{{ $data['plan']['name'] }}</span>  cuenta KLIKLER</h3>

                <h4 class="text-dark">Su solicitud del plan: {{ $data['plan']['name'] }} ha sido <span class="text-warning">rechazada.</span> </h4>
                <p>No cumplio los requisitos necesarios para su aprobación.</p>
                
                @if($data['plan']['observations'])
                <p>OBSERVACIONES: <span style="color:grey"> {{ $data['plan']['observations'] }} </span> </p>
                @endif

                <!-- <a href="" class="button"> IR A KLIKLER </a> -->
                  <a class="button"href="{{ env('ENDPOINT_FRONT') }}">  IR A KLIKLER </a>

                <br>
                <br>

                <small class="text-dark">
                    Esto es un mensaje automático, no responda este mensaje.
                </small>
            </div>
        </div>

    </div>
</body>

</html>
