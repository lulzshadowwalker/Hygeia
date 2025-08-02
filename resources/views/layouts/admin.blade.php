<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') | {{ config('app.name') }}</title>

    <!-- FontAwesomeIcons -->
    <script src="https://kit.fontawesome.com/a51f251d24.js" crossorigin="anonymous"></script>

    <!-- Vite Assets -->
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Main Content -->
    <main> @yield('content') </main>
    <div id="toaster" class="toaster"></div>
</body>

</html>
