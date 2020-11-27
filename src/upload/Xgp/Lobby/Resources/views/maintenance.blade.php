<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{ $gameName }}</title>
        <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/reset.css') }}" media="screen" />
        <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/recover.css') }}" media="screen" />
    </head>
    <body id="login">
        <div id="loginwrapper">
            <h2>{{ $closeTitle }}</h2>
            <div class="textLeft wrap-inner">
                {{ $closeReason }}
            </div>
        </div>
    </body>
</html>
