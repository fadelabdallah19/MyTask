<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=  $title ?? 'MyTask' ?></title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-32.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon-32.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon-180.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] text-[#1b1b18] min-h-screen <?php echo Auth()->check() ? 'pt-14 lg:pt-0 lg:pl-64' : ''; ?>">

    @yield('content')
    
</body>
</html>