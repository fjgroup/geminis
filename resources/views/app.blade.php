<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- SEO Meta Tags -->
        <meta name="description" content="Inicie su negocio online con hosting web profesional. WordPress, WooCommerce, dominios y más. Planes desde $10/mes. ¡Comience hoy!">
        <meta name="keywords" content="hosting web, crear sitio web, WordPress, WooCommerce, dominio, negocio online, tienda online, página web profesional">
        <meta name="author" content="FJ Group CA">
        <meta name="robots" content="index, follow">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="Inicie Su Negocio Online - Hosting Web Profesional">
        <meta property="og:description" content="Cree su sitio web profesional con nuestros planes de hosting. WordPress, WooCommerce y dominios incluidos. ¡Comience desde $10/mes!">
        <meta property="og:image" content="{{ asset('images/og-image.jpg') }}">

        <!-- Twitter -->
        <meta property="twitter:card" content="summary_large_image">
        <meta property="twitter:url" content="{{ url()->current() }}">
        <meta property="twitter:title" content="Inicie Su Negocio Online - Hosting Web Profesional">
        <meta property="twitter:description" content="Cree su sitio web profesional con nuestros planes de hosting. WordPress, WooCommerce y dominios incluidos. ¡Comience desde $10/mes!">
        <meta property="twitter:image" content="{{ asset('images/og-image.jpg') }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
