<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forbidden - POS Barokah Mart</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white w-full max-w-3xl rounded-3xl shadow-sm px-8 py-16 flex flex-col items-center text-center">

        <img src="{{ asset('images/image-forbidden.png') }}" alt="403 Forbidden Illustration"
            class="w-64 md:w-80 mb-8 object-contain">

        <h1 class="text-2xl font-bold text-gray-900 mb-3 tracking-tight">Error 403.</h1>
        <p class="text-sm text-gray-500 mb-8">Akses ditolak. Anda tidak memiliki cukup hak akses</p>

        <a href="{{ route('dashboard') }}"
            class="bg-[#38bdf8] hover:bg-[#0284c7] text-white text-sm font-medium px-8 py-2.5 rounded-full transition-colors">
            Go Back
        </a>

    </div>
</body>

</html>
