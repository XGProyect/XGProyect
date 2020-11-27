<div class="inner-box clearfix">
    <h2>{{ __('lobby::home.hm_about_title') }}</h2>
    <p>{!! __('lobby::home.hm_about_description') !!}</p>
</div>
<div class="inner-box clearfix">
    <h2>{{ __('lobby::home.hm_about_features') }}</h2>
    <ul>
        <li>{!! __('lobby::home.hm_about_description_line1') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line2') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line3') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line4') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line5') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line6') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line7') !!}</li>
        <li>{!! __('lobby::home.hm_about_description_line8') !!}</li>
    </ul>
    <a class="overlay button" href="{{$baseUrl}}/team" title="{{ __('lobby::home.hm_about_team') }}">{{ __('lobby::home.hm_about_team') }}</a> <a class="overlay button" href="{{$baseUrl}}/credits" title="{{ __('lobby::home.hm_about_credits') }}">{{ __('lobby::home.hm_about_credits') }}</a>
</div>
<div class="inner-box last clearfix">
    <h2>{{ __('lobby::home.hm_about_images') }}</h2>
    <div id="screens">
    </div>
    <br class="clearfloat" />
</div>
