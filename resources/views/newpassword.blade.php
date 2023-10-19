<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña</title>
    <style>
        /* Initial settings, Globals and Variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: 'Helvetica';
        }

        #newPassForm {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .formContainer {
            padding-top: 100px;
        }

        li { list-style: none }

        a { text-decoration: none; }

    </style>
    @vite('resources/css/app.css')
</head>

<body class="flex flex-row w-full justify-center">    
    
    <form method="POST" action="{{ route('password.processReset') }}" class="w-full flex flex-row justify-center" >

        <div class="rounded shadow-xl mt-28 p-6 flex flex-col gap-y-2 sm:w-80 md:w-100 lg:w-100 xl:w-100 2xl:w-3/12">
            @csrf

            @method('PUT')

            <div class="text-center font-bold py-4" style="color: {{ env('PRIMARY_COLOR') }}">
                Elige tu nueva contraseña
            </div>

            <div class="flex flex-col">
                <label for="password" class="text-gray-500">Nueva contraseña</label>
                <input id="password" type="password" name="password" class="@error('password') is-invalid @enderror border rounded text-lg p-1 focus:outline-{{ env("TAILWIND_PRIMARY_COLOR_CLASS") }}" />
                @error('password')
                <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="flex flex-col">
                <label for="password_confirmation" class="text-gray-500">Confirmar nueva contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" class="@error('password_confirmation') is-invalid @enderror border rounded text-lg p-1 focus:outline-{{ env("TAILWIND_PRIMARY_COLOR_CLASS") }}" />
                @error('password_confirmation')
                <div class="text-xs text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <input type="hidden" value="{{$token}}" name="token" />
            <input type="hidden" value="{{$email}}" name="email" />

            <button type="submit" class="mt-3 rounded p-2 bg-{{ env("TAILWIND_PRIMARY_COLOR_CLASS") }} text-white font-bold">
                Guardar cambios
            </button>
            
        </div>
        
    </form>

</body>
</html>
