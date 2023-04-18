<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <style type="text/css"></style>
    </head>
    <body>
        <div class="inner-box clearfix">
            <h2>{{ __('ajax/info.aj_info_about_title') }}</h2>
            <p>{!! __('ajax/info.aj_info_about_description') !!}</p>
        </div>
        <div class="inner-box clearfix">
            <h2>{{ __('ajax/info.aj_info_features') }}</h2>
            <ul>
                <li>{!! __('ajax/info.aj_info_description_line1') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line2') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line3') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line4') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line5') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line6') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line7') !!}</li>
                <li>{!! __('ajax/info.aj_info_description_line8') !!}</li>
            </ul>
            <a class="overlay button" href="index.php?page=team" title="{{ __('ajax/info.aj_info_team') }}">{{ __('ajax/info.aj_info_team') }}</a>
            <a class="overlay button" href="index.php?page=credits" title="{{ __('ajax/info.aj_info_credits') }}">{{ __('ajax/info.aj_info_credits') }}</a>
        </div>
        <div class="inner-box last clearfix">
            <h2>{{ __('ajax/info.aj_info_images') }}</h2>
            <div id="screens">
            </div>
            <br class="clearfloat" />
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                //GALLERY Fancybox
                $('#ajaxContent a.overlay').fancybox({
                    'onStart': function () {
                        $.validationEngine.closePrompt('.formError', true);
                    },
                    type: 'iframe',
                    'hideOnContentClick': true,
                    height: 433,
                    width: 480

                });
                $('#screens a').fancybox({
                    'overlayColor': '#000',
                    'hideOnContentClick': true,
                    'onStart': function () {
                        $.validationEngine.closePrompt('.formError', true);
                    }
                });
            });
        </script>
    </body>
</html>