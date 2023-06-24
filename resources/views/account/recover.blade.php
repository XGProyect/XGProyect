<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $gameName }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/home/reset.css') }}" media="screen" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/home/recover.css') }}" media="screen" />
</head>
<body id="login">
    <form id="recoverpassword" name="recoverpassword" method="POST">
        @csrf
        <h1><span>{{ $gameName }}</span></h1>
        @if(Session::has('message'))
        <div id="error">
            <p>{{ Session::get('message') }}</p>
        </div>
        @endif
        <div id="loginwrapper">
            <h2>{{ __('account/recover.re_send_pwd_title', ['game' => $gameName]) }}</h2>
            <div class="textLeft wrap-inner">
                <label for="login">{{ __('account/recover.re_label') }}</label>
                <input type="text" name="email" id="login" tabindex="1" class="input" />
                <input type="submit" value="{{ __('account/recover.re_value') }}" tabindex="2" class="start" />
            </div>
            <div id="advice">
                <p>{{ __('account/recover.re_advice') }}</p>
            </div>
            <br class="clear" />
        </div>
    </form>
</body>
</html>
