@extends('master.game', ['noTopnav' => true, 'noLeftMenu' => true])

@section('content')
<br>
<table cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td colspan="1">
                <img src="{{ asset('assets/upload/skins/xgproyect/planets/small/s_' . $planetImage . '.jpg') }}" />
            </td>
            <td colspan="2" style="vertical-align: top">
                <p>{{ __('game/planetlayer.description') }}</p>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="c">{{ __('game/planetlayer.rename') }}</td>
        </tr>
        <tr>
            <td colspan="3">
                <form id="planetMaintenance" method="POST" action="game.php?page=planetlayer">
                    <input type="hidden" id="newPlanetName" name="newPlanetName" value="{{ $defaultName }}">
                    <input type="text" maxlength="20" size="25" id="planetName" name="planetName" value="{{ $defaultName }}">
                    <input type="submit" value="{{ __('game/planetlayer.rename') }}" name="action">
                </form>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="c">
                @if ($mainPlanet)
                    {{ __('game/planetlayer.abandon_homeplanet') }}
                @else
                    @if ($isMoon)
                        {{ __('game/planetlayer.abandon_moon') }}
                    @else
                        {{ __('game/planetlayer.abandon_colony') }}
                    @endif
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3">
                @if (!$isMoon)
                    @if ($withColonies)
                        {{ __('game/planetlayer.abandon_homeplanet_with_colonies') }}
                    @else
                        {{ __('game/planetlayer.abandon_homeplanet_unique') }}
                    @endif
                @endif
            </td>
        </tr>
        @if ($withColonies)
        <tr>
            <td>{{ $planetCoords }}</td>
            <td>{{ $planetName }}</td>
            <td>
                <a onclick="document.getElementById('validate').style.display='block';" style="cursor: pointer">
                    @if ($mainPlanet)
                        {{ __('game/planetlayer.abandon_homeplanet_button') }}
                    @else
                        @if ($isMoon)
                            {{ __('game/planetlayer.abandon_moon_button') }}
                        @else
                            {{ __('game/planetlayer.abandon_colony_button') }}
                        @endif
                    @endif
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <form id="planetMaintenanceDelete" method="POST" action="game.php?page=planetlayer">
                    <div class="validate" id="validate" style="display:none;">
                        <p>{{ __('game/planetlayer.abandon_confirm_message', ['coords' => $planetCoords]) }}</p>
                        <input type="password" name="password" maxlength="1024" size="25"/>
                        <input type="submit" value="{{ __('game/planetlayer.abandon_confirm') }}"/>
                    </div>
                </form>
            </td>
        </tr>
        @endif
    </tbody>
</table>
@endsection