@extends('master.game')

@section('content')
<table border="0" cellpadding="0" cellspacing="1" width="750px">
    <tbody>
        <tr height="20px" valign="left">
            <td class="c" colspan="{{ $planetsAmount }}">{{ __('game/empire.em_imperium_title') }}</td>
        </tr>
        <tr height="75px">
            <th width="75px">{{ __('game/empire.em_planet') }}</th>
            @foreach ($image as $item)
            <th width="75px">
                <a href="game.php?page=overview&cp={{ $item['planetId'] }}&re=0">
                    <img src="{{ asset('assets/upload/skins/xgproyect/planets/small/s_' . $item['planetImage'] . '.jpg') }}" border="0" width="80px" height="80px" alt="{{ $item['planetName'] }}"/>
                </a>
            </th>
            @endforeach
        </tr>
        <tr height="20px">
            <th width="75px">{{ __('game/empire.em_name') }}</th>
            @foreach ($name as $item)
            <th width="75px">
                {{ $item['planetName'] }}
            </th>
            @endforeach
        </tr>
        <tr height="20px">
            <th width="75px">{{ __('game/empire.em_coords') }}</th>
            @foreach ($coords as $item)
            <th width="75px">
                <a href="game.php?page=galaxy&mode=3&galaxy={{ $item['planetGalaxy'] }}&system={{ $item['planetSystem'] }}">{!! $item['planetCoords'] !!}</a>
            </th>
            @endforeach
        </tr>
        <tr height="20px">
            <th width="75px">{{ __('game/empire.em_fields') }}</th>
            @foreach ($fields as $item)
            <th width="75px">
                {{ $item['planetFieldCurrent'] }} / {{ $item['planetFieldMax'] }}
            </th>
            @endforeach
        </tr>
        <tr>
            <td class="c" colspan="{{ $planetsAmount }}" align="left">{{ __('game/empire.em_resources') }}</td>
        </tr>
        <tr>
            <th width="75px">{{ __('game/global.metal') }}</th>
            @foreach ($metalRow as $item)
            <th width="75px">
                <a href="game.php?page=resources&cp={{ $item['planetId'] }}&re=0&planettype={{ $item['planetType'] }}">{{ $item['planetCurrentAmount'] }}</a> / {{ $item['planetProduction'] }}
            </th>
            @endforeach
        </tr>
        <tr>
            <th width="75px">{{ __('game/global.crystal') }}</th>
            @foreach ($crystalRow as $item)
            <th width="75px">
                <a href="game.php?page=resources&cp={{ $item['planetId'] }}&re=0&planettype={{ $item['planetType'] }}">{{ $item['planetCurrentAmount'] }}</a> / {{ $item['planetProduction'] }}
            </th>
            @endforeach
        </tr>
        <tr>
            <th width="75px">{{ __('game/global.deuterium') }}</th>
            @foreach ($deuteriumRow as $item)
            <th width="75px">
                <a href="game.php?page=resources&cp={{ $item['planetId'] }}&re=0&planettype={{ $item['planetType'] }}">{{ $item['planetCurrentAmount'] }}</a> / {{ $item['planetProduction'] }}
            </th>
            @endforeach
        </tr>
        <tr>
            <th width="75px">{{ __('game/global.energy') }}</th>
            @foreach ($energyRow as $item)
            <th width="75px">
                {{ $item['usedEnergy'] }} / {{ $item['maxEnergy'] }}
            </th>
            @endforeach
        </tr>
        <tr>
            <td class="c" colspan="{{ $planetsAmount }}" align="left">{{ __('game/empire.em_resources') }}</td>
        </tr>
        @foreach ($resources as $item)
        <tr>
            {!! $item['value'] !!}
        </tr>
        @endforeach
        <tr>
            <td class="c" colspan="{{ $planetsAmount }}" align="left">{{ __('game/empire.em_buildings') }}</td>
        </tr>
        @foreach ($facilities as $item)
        <tr>
            {!! $item['value'] !!}
        </tr>
        @endforeach
        <tr height="20px">
            <td class="c" colspan="{{ $planetsAmount }}" align="left">{{ __('game/empire.em_defenses') }}</td>
        </tr>
        @foreach ($defenses as $item)
        <tr>
            {!! $item['value'] !!}
        </tr>
        @endforeach
        @foreach ($missiles as $item)
        <tr>
            {!! $item['value'] !!}
        </tr>
        @endforeach
        <tr height="20px">
            <td class="c" colspan="{{ $planetsAmount }}" align="left">{{ __('game/empire.em_technology') }}</td>
        </tr>
        @foreach ($tech as $item)
        <tr>
            {!! $item['value'] !!}
        </tr>
        @endforeach
        <tr height="20px">
            <td class="c" colspan="{{ $planetsAmount }}" align="left">{{ __('game/empire.em_ships') }}</td>
        </tr>
        @foreach ($fleet as $item)
        <tr>
            {!! $item['value'] !!}
        </tr>
        @endforeach
    </tbody>
</table>
@endsection