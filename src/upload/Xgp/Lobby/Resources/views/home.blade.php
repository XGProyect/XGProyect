<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="language" content="es">
    <meta name="author" content="XG Proyect">
    <meta name="publisher" content="XG Proyect">
    <meta name="copyright" content="XG Proyect">
    <meta name="audience" content="all">
    <meta name="Expires" content="never">
    <meta name="Keywords" content="{{ __('lobby::home.hm_keywords') }}">
    <meta name="Description" content="{{ __('lobby::home.hm_description') }}">
    <meta name="robots" content="index,follow">
    <meta name="Revisit" content="After 14 days">
    <title>{{ $servername }}</title>
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/reset.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/forms.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ Module::asset('lobby:css/all.css') }}">
    <script type="text/javascript" src="{{ Module::asset('lobby:js/jquery.tools.min.js') }}"></script><style type="text/css"></style>
    <script type="text/javascript" src="{{ Module::asset('lobby:js/jquery.easing-1.3.pack.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('lobby:js/jquery.jparallax.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('lobby:js/jquery.fancybox-1.3.1.pack.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('lobby:js/jquery.validationEngine.modified.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('lobby:js/xgproyect.js') }}"></script>
    <script type="text/javascript" src="{{ Module::asset('lobby:js/test.js') }}"></script>
    <script type="text/javascript">
    // <![CDATA[
    (function($) {
        $.fn.validationEngineLanguage = function(){};
        $.validationEngineLanguage = {
            newLang: function() {
                $.validationEngineLanguage.allRules = {
                    "required":{    			// Add your regex rules here, you can take telephone as an example
                        "regex":"none",
                        "alertText":"{{ __('lobby::home.hm_field_required') }}",
                        "alertTextCheckboxMultiple":"Toma una decisi\u00f3n",
                        "alertTextCheckboxe":"{{ __('lobby::home.hm_must_accept_tandc') }}"},
                    "length":{
                        "regex":"none",
                        "alertText":"{{ __('lobby::home.hm_username_length') }}"},
                    "pwLength":{
                        "regex":"none",
                        "alertText":"{{ __('lobby::home.hm_password_length') }}"},
                    "maxCheckbox":{
                        "regex":"none",
                        "alertText":"* Checks allowed Exceeded"},
                    "minCheckbox":{
                        "regex":"none",
                        "alertText":"* Bitte wähle ",
                        "alertText2":" Optionen"},
                    "confirm":{
                        "regex":"none",
                        "alertText":"* Diese Felder passen nicht zusammen"},
                    "telephone":{
                        "regex":"/^[0-9\-\(\)\ ]+$/",
                        "alertText":"* Unzulässige Telefonnummer"},
                    "email":{
                        "regex":"/^[a-zA-Z0-9_\\.\\-]+\\@([a-zA-Z0-9\\-]+\\.)+[a-zA-Z0-9]{2,4}$/",
                        "alertText":"{{ __('lobby::home.hm_valid_email_address') }}"},
                    "date":{
                            "regex":"/^[0-9]{4}\-\[0-9]{1,2}\-\[0-9]{1,2}$/",
                            "alertText":"* Invalid date, must be in YYYY-MM-DD format"},
                    "onlyNumber":{
                        "regex":"/^[0-9\ ]+$/",
                        "alertText":"* Bitte nur Nummern"},
                    "noSpecialCharacters":{
                        "regex":"/^[a-zA-Z0-9\\s_\\-]+$/",
                        "alertText":"{{ __('lobby::home.hm_not_valid_characters') }}"},
                    "noBeginOrEndUnderscore":{
                        "regex":/^([^_]+(.*[^_])?)?$/,
                        "alertText":"{{ __('lobby::home.hm_username_underscore') }}"},
                    "noBeginOrEndHyphen":{
                        "regex":/^([^\-]+(.*[^\-])?)?$/,
                        "alertText":""},
                    "noBeginOrEndWhitespace":{
                        "regex":/^([^\s]+(.*[^\s])?)?$/,
                        "alertText":"{{ __('lobby::home.hm_username_space') }}"},
                    "notMoreThanThreeUnderscores":{
                        "regex":/^[^_]*(_[^_]*){0,3}$/,
                        "alertText":"{{ __('lobby::home.hm_username_many_underscore') }}"},
                    "notMoreThanThreeHyphen":{
                        "regex":/^[^\-]*(\-[^\-]*){0,3}$/,
                        "alertText":""},
                    "notMoreThanThreeWhitespaces":{
                        "regex":/^[^\s]*(\s[^\s]*){0,3}$/,
                        "alertText":"{{ __('lobby::home.hm_username_many_spaces') }}"},
                    "noCollocateUnderscores":{
                        "regex":/^[^_]*(_[^_]+)*_?$/,
                        "alertText":"{{ __('lobby::home.hm_username_underscore_continued') }}"},
                    "noCollocateHyphen":{
                        "regex":/^[^\-]*(\-[^\-]+)*-?$/,
                        "alertText":""},
                    "noCollocateWhitespaces":{
                        "regex":/^[^\s]*(\s[^\s]+)*\s?$/,
                        "alertText":"{{ __('lobby::home.hm_username_spaces_continued') }}"},
                    "ajaxUser":{
                        "file":"../validateUser.php",
                        "alertTextOk":"{{ __('lobby::home.hm_username_available') }}",
                        "alertTextLoad":"{{ __('lobby::home.hm_username_loading') }}",
                        "alertText":"{{ __('lobby::home.hm_username_not_available') }}"},
                    "ajaxName":{
                        "file":"../validateUser.php",
                        "alertTextOk":"{{ __('lobby::home.hm_username_available') }}",
                        "alertTextLoad":"{{ __('lobby::home.hm_username_available') }}"},
                        "alertText":"{{ __('lobby::home.hm_username_not_available') }}",
                    "onlyLetter":{
                        "regex":"/^[a-zA-Z\ \']+$/",
                        "alertText":"{{ __('lobby::home.hm_only_characters') }}"}
                    }
                }
            }
        })(jQuery);
    var universeDistinctions = [];

    $(document).ready(function() {
        $(".zebra tr:odd").addClass("alt");
        $.validationEngineLanguage.newLang()
        $.validationEngine.buildPrompt("{{$divId}}", "{{$message}}", "error");
    });
    // ]]>
    </script>
<style type="text/css"> body {margin:0; padding:0;}</style></head>
<body>
	<div id="start">
	    <div id="header">
            <h1>
                <img src="{{ $gameLogo }}" width="200px">
                <a href="./" title="{{ __('lobby::home.hm_hidden_title') }}">
                    {{ __('lobby::home.hm_hidden_title') }}
                </a>
            </h1>
            <a id="loginBtn" href="javascript:void(0)" title="Login">
                {{ __('lobby::home.hm_login_button') }}
            </a>
            <div id="login">
                <form id="loginForm" name="loginForm" method="post" action="{{$baseUrl}}/signin">
                    @csrf <!-- {{ csrf_field() }} -->
                    <input type="hidden" name="kid" value="">
                        <div class="input-wrap">
                            <label for="serverLogin">
                                {{ __('lobby::home.hm_universe') }}
                            </label>
			                <div class="black-border">
                                <select class="js_uniUrl" id="serverLogin" name="uni">
                                    <option value="0">{{ __('lobby::home.hm_universe_name') }}</option>
                                </select>
                            </div>
			            </div>
                        <div class="input-wrap">
                            <label for="usernameLogin">{{ __('lobby::home.hm_username_mail') }}</label>
                            <div class="black-border">
                                <input class="js_userName" type="text" onkeydown="hideLoginErrorBox();" id="usernameLogin" name="login" value="">
                            </div>
                        </div>
                        <div class="input-wrap">
                            <label for="passwordLogin">{{ __('lobby::home.hm_password') }}</label>
                            <div class="black-border">
                                <input type="password" onkeydown="hideLoginErrorBox();" id="passwordLogin" name="pass" maxlength="20">
                            </div>
                        </div>
                        <input type="submit" id="loginSubmit" value="{{ __('lobby::home.hm_login_button') }}">
                        <a href="#" id="pwLost" target="_blank" title="{{ __('lobby::home.hm_password_forgot') }}">{{ __('lobby::home.hm_password_forgot') }}</a>
                        <p id="TermsAndConditionsAcceptWithLogin">
                            {{ __('lobby::home.hm_terms_accept') }} <a class="" href="{{$baseUrl}}/home/terms" target="_blank" title="{{ __('lobby::home.hm_terms') }}">{{ __('lobby::home.hm_terms') }}</a>
                        </p>
                </form>
			</div>
		</div>
		<div id="content" class="clearfix">
			<div id="subscribe">
                <form id="subscribeForm" name="subscribeForm" method="POST" onsubmit="changeAction(&#39;register&#39;,&#39;subscribeForm&#39;);" action="">
                    @csrf <!-- {{ csrf_field() }} -->
                    <input type="hidden" name="v" value="3">
                    <input type="hidden" name="step" value="validate">
                    <input type="hidden" name="kid" value="">
                    <input type="hidden" name="errorCodeOn" value="1">
                    <input type="hidden" name="is_utf8" value="1">
                    
                    <h2>{{ __('lobby::home.hm_play_for_free') }}</h2>
                    <div class="input-wrap first">
                        <label for="server">{{ __('lobby::home.hm_universe') }}</label>
                        <div id="server" style="position:relative;">
                            <table cellspacing="0" cellpadding="0" onclick="switch_uni_selection()" onmouseover="this.style.cursor=&#39;pointer&#39;" class="server_table" style="cursor: pointer;">
                                <tbody>
                                    <tr>
                                        <td id="uni_select_box" class="select" style="height:19px;overflow:hidden;">
                                            <span id="uni_name" class="">{{ __('lobby::home.hm_universe_name') }}</span>
                                        </td>
                                        <td style="width:18px; background: url('{{ Module::asset('lobby:images/dropdownmenu_arrow.png') }}') no-repeat scroll 0 0 #8D9AA7;"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <input class="js_uniUrl" type="hidden" name="uni_url" id="uni_domain" value="">
                            <div id="uni_selection" style="display: none;">
                                <script type="text/javascript">
                                <!--
                                    select_uni('{{$baseUrl}}'.replace('http://', '').replace('https://', ''), '{{ __('lobby::home.hm_universe_name') }}','');
                                //-->
                                </script>
                                <div id="row-0" class="server-row " title="" onclick="select_uni('{{$baseUrl}}','{{ __('lobby::home.hm_universe_name') }}');" onmouseover="highlightRow(&#39;row-0&#39;);this.style.cursor=&#39;pointer&#39;" onmouseout="unHighlightRow(&#39;row-0&#39;);">
                                    <span class="uni_span ">{{ __('lobby::home.hm_universe_name') }}</span>
                                </div>
                            </div>
                        </div>
					</div>
					<div class="input-wrap">
                        <label for="username">{{ __('lobby::home.hm_username') }}</label>
						<div class="black-border">
                            <!-- validate options dürfen nicht umgebrochen werden, da das plugin sonst nicht mehr funktioniert  -->
                            <input id="username" class="js_userName validate[required,custom[noSpecialCharacters],custom[noBeginOrEndUnderscore],custom[noBeginOrEndWhitespace],custom[noBeginOrEndHyphen],custom[notMoreThanThreeUnderscores],custom[notMoreThanThreeWhitespaces],custom[notMoreThanThreeHyphen],custom[noCollocateUnderscores],custom[noCollocateWhitespaces],custom[noCollocateHyphen],length[3,20]]" type="text" name="character" value="{{ $userName }}">
						</div>
					</div>
                    <div class="input-wrap">
                        <label for="password">{{ __('lobby::home.hm_password') }}</label>
                        <div class="black-border">
                            <input class="validate[required,pwLength[8,20]]" type="password" id="password" name="password" value="" maxlength="20">
                        </div>
                    </div>
					<div class="input-wrap">
                        <label for="email">{{ __('lobby::home.hm_mail_address') }}</label>
						<div class="black-border">
                            <input class="validate[required,custom[email],length[0,255]]" type="text" id="email" name="email" value="{{ $userEmail }}">
						</div>
					</div>
					<div class="input-wrap">
                        <div id="securePwd">
							<p>{{ __('lobby::home.hm_password_level') }}</p>
							<div class="valid-icon invalid"></div>
							<div class="securePwdBarBox">
								<div id="securePwdBar"></div>
							</div>
							<br class="clearfloat">
						</div>
					</div>
					<div id="submitWrap">
						<input class="validate[required]" type="checkbox" id="agb" name="agb">
                        <label>
                            <span>{{ __('lobby::home.hm_accept') }} <a class="" target="_blank" href="{{$baseUrl}}/home/terms" title="{{ __('lobby::home.hm_terms') }}">{{ __('lobby::home.hm_terms') }}</a> {{ __('lobby::home.hm_and') }} <a class="" target="_blank" href="{{$baseUrl}}/home/policy" title="{{ __('lobby::home.hm_policy') }}">{{ __('lobby::home.hm_policy') }}</a></span>
						</label>
                        <div onclick="if($.validationEngine.submitValidation(&#39;subscribeForm&#39;)) {document.forms[&#39;subscribeForm&#39;].submit();}">
                            <input type="submit" id="regSubmit" value="{{ __('lobby::home.hm_register') }}">
                        </div>
					</div>
				</form>
                			</div>
			<div id="contentWrap">
				<div id="menu" style="background-position: 15px -33px;">
					<ul id="tabs">
						<li><a id="tab1" href="{{$baseUrl}}/welcome" class="current">{{ __('lobby::home.hm_home') }}</a></li>
                        <li><a id="tab2" href="{{$baseUrl}}/about">{{ __('lobby::home.hm_about') }}</a></li>
                        <li><a id="tab3" href="{{$baseUrl}}/media">{{ __('lobby::home.hm_media') }}</a></li>
					</ul>
                                            <a id="tab4" href="{{ $forumUrl }}" target="_blank">{{ __('lobby::home.hm_forum') }}</a>
                                        					<br class="clearfloat">
				</div>
				<div id="tabContent">
					<div id="ajaxContent">
</div>
				</div>
				<div id="contentFooter"></div>
			</div>
		</div>
		<div id="push"></div>
	</div>
        <div id="footer">
            <div id="footerContent">
                <p id="copyright">Powered by <a href="https://xgproyect.org/" target="_blank" title="XG Proyect {{ $version }}">XG Proyect</a> © 2008 - {{ $year }}.</p>
            </div>
        </div>
    	<!-- OVERLAY DIVISION -->
        <script type="text/javascript">
            JSLoca = new Array('{{ __('lobby::home.hm_login_button') }}', '{{ __('lobby::home.hm_close_button') }}');
        </script>
        <script type="text/javascript" src="{{ Module::asset('lobby:js/xgproyect.js') }}"></script>
        <script type="text/javascript" src="{{ Module::asset('lobby:js/xgproyect.start.js') }}"></script>
        <div id="fancybox-tmp"></div>
        <div id="fancybox-loading"><div>
    </div>
</div>
<div id="fancybox-overlay"></div>
    <div id="fancybox-wrap">
        <div id="fancybox-outer">
            <div class="fancy-bg" id="fancy-bg-n"></div>
            <div class="fancy-bg" id="fancy-bg-ne"></div>
            <div class="fancy-bg" id="fancy-bg-e"></div>
            <div class="fancy-bg" id="fancy-bg-se"></div>
            <div class="fancy-bg" id="fancy-bg-s"></div>
            <div class="fancy-bg" id="fancy-bg-sw"></div>
            <div class="fancy-bg" id="fancy-bg-w"></div>
            <div class="fancy-bg" id="fancy-bg-nw"></div>
            <div id="fancybox-inner"></div>
                <a id="fancybox-close"></a>
                <a href="javascript:;" id="fancybox-left">
                    <span class="fancy-ico" id="fancybox-left-ico"></span>
                </a>
                <a href="javascript:;" id="fancybox-right">
                    <span class="fancy-ico" id="fancybox-right-ico"></span>
                </a>
            </div>
        </div>
    </body>
</html>
