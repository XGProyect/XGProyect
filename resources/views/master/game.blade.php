<!DOCTYPE html>
<html lang="{{ __('game/global.lang_code') }}">
    <head>
        <title>{{ $gameTitle }}</title>
        <link rel="shortcut icon" href="favicon.ico">
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
        @if (!isset($noTopnav))
        <x-topnav />
        @endif

        @if (!isset($noLeftMenu))
        <x-leftmenu />
        @endif

        <center>
            @yield('content')
        </center>

        <script>
            messageboxHeight = 0;
            errorboxHeight = 0;
            contentbox = document.getElementById('content');
        </script>

        <div id='messagebox'>
            <center></center>
        </div>
        <div id='errorbox'>
            <center></center>
        </div>

        <script>
        headerHeight = 81;
        errorbox.style.top = parseInt(headerHeight + messagebox.offsetHeight + 5) + 'px';
        contentbox.style.top = parseInt(headerHeight + errorbox.offsetHeight + messagebox.offsetHeight + 10) + 'px';
        if (navigator.appName == 'Netscape'){
            if (window.innerWidth < 1020){
                document.body.scroll = 'no';
            }

            contentbox.style.height = parseInt(window.innerHeight) - messagebox.offsetHeight - errorbox.offsetHeight - headerHeight - 20;

            if(document.getElementById('resources')) {
                document.getElementById('resources').style.width = (window.innerWidth * 0.4);
            }
        }
        else {
            if (document.body.offsetWidth < 1020){
                document.body.scroll = 'no';
            }

            contentbox.style.height = parseInt(document.body.offsetHeight) - messagebox.offsetHeight - headerHeight - errorbox.offsetHeight - 20;
            document.getElementById('resources').style.width = (document.body.offsetWidth * 0.4);
        }
        </script>
    </body>
</html>