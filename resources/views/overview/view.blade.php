@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table width="519">
        <tr>
            <td class="c" colspan="4">
                <a href="#" title="{{ __('game/overview.ov_abandon_rename') }}" onclick="f('game.php?page=planetlayer', '{{ __('game/overview.ov_abandon_rename') }} {{ $planetName }}')">
                    {{ __('game/overview.ov_planet') }} "{{ $planetName }}"
                </a> ({{ $username }})
            </td>
        </tr>
        {!! $newMessage !!}
        <tr>
            <th scope="row">{{ __('game/overview.ov_server_time') }}</th>
            <th role="cell" colspan="3">{{ $dateTime }}</th>
        </tr>
        <tr>
            <td colspan="4" class="c">{{ __('game/overview.ov_events') }}</td>
        </tr>
        {!! $fleetList !!}
        <tr>
            <th role="cell">{!! $moonImg !!}<br>{{ $moon }}</th>
            <th role="cell" colspan="2"><img src="{{ asset('assets/upload/skins/xgproyect/planets/' . $planetImage . '.jpg') }}" height="200" width="200" alt=""/><br>{!! $building !!}</th>
            <th role="cell" class="s">
                <table role="presentation" class="s" align="top" border="0">
                    <tr>{!! $otherPlanets !!}</tr>
                </table>
            </th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/overview.ov_diameter') }}</th>
            <th role="cell" colspan="3">{{ $planetDiameter }} {{ __('game/overview.ov_distance_unit') }} ({{ $planetCurrentFields }} / {{ $planetMaxFields }} {{ __('game/overview.ov_fields') }})</th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/overview.ov_temperature') }}</th>
            <th role="cell" colspan="3">
                {{ __('game/overview.ov_aprox') }} {{ $planetMinTemp }}{{ __('game/overview.ov_temp_unit') }} {{ __('game/overview.ov_to') }} {{ $planetMaxTemp }}{{ __('game/overview.ov_temp_unit') }}
            </th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/overview.ov_position') }}</th>
            <th role="cell" colspan="3">
                <a href="game.php?page=galaxy&mode=0&galaxy={{ $galaxyGalaxy }}&system={{ $galaxySystem }}">[{{ $galaxyGalaxy }}:{{ $galaxySystem }}:{{ $galaxyPlanet }}]</a>
            </th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/overview.ov_points') }}</th>
            <th role="cell" colspan="3">{!! $userRank !!}</th>
        </tr>
    </table>
</div>
@endsection