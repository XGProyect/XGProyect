<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<div id="header_top">
    <table class="header" style="margin:0 auto">
        <tr class="header">
            <td class="header" style="width:5;">
                <table class="header">
                    <tr class="header">
                        <td class="header">
                            <img src="{{ asset('upload/skins/xgproyect/planets/small/s_' . $planetImage . '.jpg') }}" height="50" width="50">
                        </td>
                        <td class="header">
                            <table class="header">
                                <select size="1" onchange="location = this.options[this.selectedIndex].value;">
                                @foreach ($planetList as $planet)
                                <option {{ $planet['selected'] }} value="{{ $planet['value'] }}">
                                    {{ $planet['text'] }}
                                </option>
                                @endforeach
                                </select>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="header">
                <table class="header" id="resources" cellspacing="0" cellpadding="0" padding-right="30">
                    <tr class="header" style="text-align:center">
                        @foreach ($resources as $resource)
                        <td width="85" class="header">
                            <img src="{{ asset('upload/skins/xgproyect/resources/' . $resource  . '.gif') }}" width="42" height="22" alt="{{ __('game/global.' . $resource) }}" title="{{ __('game/global.' . $resource) }}" />
                        </td>
                        @endforeach
                    </tr>
                    <tr class="header" style="text-align:center">
                        @foreach ($resources as $resource)
                        <td class="header" width="85">
                            <span style="font-weight:700;font-style: italic;">{{ __('game/global.' . $resource) }}</span>
                        </td>
                        @endforeach
                    </tr>
                    <tr class="header" style="text-align:center">
                        @foreach ($resourcesAmount as $resource)
                        <td class="header" width="90">{!! $resource !!}</td>
                        @endforeach
                    </tr>
                </table>
            </td>

            <td class="header">
                <table class="header">
                    <tr class="header">
                        @foreach ($officers as $officer)
                        <td style="margin: 0 auto;" width="35px" class='header'>
                            <a href="game.php?page=officier" accesskey="o">
                                <img style="border:0;" src="{{ asset('upload/skins/xgproyect/premium/' . $officer['icon'] .'.gif') }}" width="32" height="32" alt="{{ $officer['name'] }}" onmouseover="return overlib('<table width=390px><tr><td class=c>{{ $officer['name'] }}</td></tr><tr><th style=text-align:left>{{ $officer['status'] }}</th></tr></table>');" onmouseout="return nd();">
                            </a>
                        </td>
                        @endforeach
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <x-notice :color="$color" :message="$message" />
</div>
