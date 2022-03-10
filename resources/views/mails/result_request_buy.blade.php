<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <style>
        body{
            font-family:Arial, Helvetica, sans-serif
        }

        .button{

            background:rgb(236, 154, 0);
            padding: 16px;
            color: white !important;
            text-decoration: none;
            font-family: 'Franklin Gothic', 'Arial Narrow', Arial, sans-serif;
            /* border-radius:5px; */
            font-size: 20px;
            /* line-height: 1.5; */
            display: inline-block;
        }

        .button:hover{
            background:rgb(255, 180, 40);
        }
      </style>
</head>

<body>
    <br>
    <br>
    <div class="container2">

        <div class="card" style="border:1px rgb(217, 217, 217) solid;padding: 30px;border-radius: 5px;max-width: 700px;background:rgb(15, 14, 14);border:solid 4px grey">
            <div style="text-align:center;">
                <img style="width: 200px;" src="
                    {{$message->embed(asset('public/images/LOGO-133x42.png'))}}
                    " class="logo-mail" style="" data-auto-embed="attachment"/>


            <br>
            <h2 style="color:orange">Notificación Bitcoin Ecuador</h2>



            <h4 style="color:rgb(214, 212, 212)" > Su solicitud de compra de {{$data['amount']}} $ BTC en "Bitcoin Ecuador" ha sido
                @if ($data['result'] == 'completed')
                <span style="color:rgb(35, 235, 35)">rechazada</span>
                @else
                <span style="color:rgb(255, 71, 71)">confirmada</span>
                @endif

                . </h4>
            <br>

            {{-- <a href="" class="button"> Confirmar cuenta Bitcoin Ecuador </a> --}}
            <a href="{{ env('ENDPOINT_BACK') }}" class="button"> Ir a Bitcoin Ecuador </a>

            <br>
            <br>


            <p class="" style="text-align:center;line-height:1;color:rgb(214, 212, 212)">
                Esto es un mensaje automático, por favor no responda este mensaje.
            </p>
        </div>
        </div>

    </div>
</body>
</html>
