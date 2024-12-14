<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', 'Default Title')</title>
        <!-- Add your CSS here -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    </head>
    <body>
        <div class="sidebar">
            @include('sidebar')
        </div>

        <main>
            @yield('content')
        </main>

        <footer>
            @include('footer')
        </footer>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>