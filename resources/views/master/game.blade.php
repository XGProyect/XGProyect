<!DOCTYPE html>
<html lang="{{ __('game/global.lang_code') }}">
    <head>
        <title>{{ $gameTitle }}</title>
        <link rel="shortcut icon" href="favicon.ico">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/base.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/default.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/redesign.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/formate.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/upload/skins/xgproyect/formate.css') }}">
        <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
        <meta name="generator" content="XG Proyect {{ config('version.files') }}" />
        <script type="text/javascript" src="{{ asset('assets/js/overlib-min.js') }}"></script>
        @yield('metatags')
    </head>
    <body>
        <div id="container">
            <div id="menu">
                @if (!isset($noLeftMenu))
                <x-game.leftmenu />
                @endif
            </div>
            <div id="page-content">
                <div id="navbar">
                    @if (!isset($noTopnav))
                    <x-game.topnav />
                    @endif
                </div>
                <div id="content" role="main">
                    <br>
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>