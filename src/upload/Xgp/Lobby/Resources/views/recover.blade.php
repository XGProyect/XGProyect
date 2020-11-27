<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{{$gameName}}</title>
        <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/reset.css') }}" media="screen" />
        <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/recover.css') }}" media="screen" />
    </head>
    <body id="login">
        <form name="recoverpassword"  method="post" action="{{$baseUrl}}/recover/request">
            <h1><span>{{$gameName}}</span></h1>
            <div id="error" style="{{$display}}">
                <p>{{$errorMsg}}</p>
            </div>
            <div id="loginwrapper">
                <h2>{{ __('lobby::recover.ma_send_pwd_title') }}</h2>
                <div class="textLeft wrap-inner">
                    <label for="login">{{ __('lobby::recover.ma_label') }}</label>
                    <input type="text" name="email" id="login" tabindex="1" class="input" />
                    <input type="submit" value="{{ __('lobby::recover.ma_value') }}" tabindex="2" class="start" />
                </div>
                <div id="advice">
                    <p>{{ __('lobby::recover.ma_advice') }}</p>
                </div>
                <br class="clear" />
            </div>
        </form>
    </body>
</html>
