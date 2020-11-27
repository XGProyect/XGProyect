<div class="inner-box clearfix">
    <h2>{!! __('lobby::home.hm_welcome_conquer_universe') !!}</h2>
    <p>{!! __('lobby::home.hm_welcome_description') !!}</p>
    <a class="overlay button" href="home/rules" title="{{ __('lobby::home.hm_welcome_rules') }}">{{ __('lobby::home.hm_welcome_rules') }}</a>
</div>
<div id="trailer" class="inner-box last clearfix">
    <h2 id="trailer">{{ __('lobby::home.hm_welcome_trailer') }}</h2>
    <div id="flashTrailer">
        <object width="425" height="270">
            <param name="movie" value="{{ Module::asset('lobby:flash/flashtrailer.swf') }}">
            <param name="wmode" value="opaque">
            <embed src="{{ Module::asset('lobby:flash/flashtrailer.swf') }}" type="application/x-shockwave-flash" wmode="opaque" allowfullscreen="true" allowscriptaccess="always" width="425" height="270">
        </object>
    </div>
</div>
<script type="text/javascript">
    // Trailer-Klick-Error-Ausblendung.
    $('#trailer').click(function () {
        $.validationEngine.closePrompt('.formError', true);
    });

    $('#ajaxContent a.overlay').fancybox({
        'onStart': function () {
            $.validationEngine.closePrompt('.formError', true);
        },
        type: 'iframe',
        'hideOnContentClick': true,
        height: 433,
        width: 480
    });
</script>