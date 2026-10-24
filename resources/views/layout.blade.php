<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>MEMO</title>

        @vite('resources/css/app.css')

    </head>
    <body class="vh-100 d-flex justify-content-center text-center">
        <div class="w-75 mt-3">
            <div class="text-black-50 text-start border-bottom mt-5">
                <h1>MEMO</h1>
            </div>

            <div>
                @yield('content')
            </div>
        </div>

    </body>
</html>
