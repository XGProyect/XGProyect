@extends('master.game')

@section('content')
<form action="" method="post" role="form">
    <table width="569">
        <tbody>
            <tr>
                <td class="c" colspan="5">{{ $Production_of_resources_in_the_planet }}</td>
            </tr><tr>
                <th height="22">&nbsp;</th>
                <th width="60">{{ __('game/global.metal') }}</th>
                <th width="60">{{ __('game/global.crystal') }}</th>
                <th width="60">{{ __('game/global.deuterium') }}</th>
                <th width="60">{{ __('game/global.energy') }}</th>
                <th class="k"><input name="action" value="{{ __('game/resources.rs_calculate') }}" type="submit"></th>
            </tr><tr>
                <th scope="row" height="22">{{ __('game/resources.rs_basic_income') }}</th>
                <td class="k">{{ $metal_basic_income }}</td>
                <td class="k">{{ $crystal_basic_income }}</td>
                <td class="k">{{ $deuterium_basic_income }}</td>
                <td class="k">{{ $energy_basic_income }}</td>
            </tr>
            {!! $resource_row !!}
            <tr>
                <th scope="row" height="22">{{ __('game/technologies.research_plasma_technology') }} ({{ __('game/global.level') }}: {{ $plasma_level }})</th>
                <td class="k">{!! $plasma_metal !!}</td>
                <td class="k">{!! $plasma_crystal !!}</td>
                <td class="k">{!! $plasma_deuterium !!}</td>
                <td class="k">0</td>
            </tr><tr>
                <th scope="row" height="22">{{ __('game/resources.rs_storage_capacity') }}</th>
                <td class="k">{!! $planet_metal_max !!}</td>
                <td class="k">{!! $planet_crystal_max !!}</td>
                <td class="k">{!! $planet_deuterium_max !!}</td>
                <td class="k">0</td>
            </tr><tr>
                <th scope="row" height="22">{{ __('game/resources.rs_sum') }}</th>
                <td class="k">{!! $metal_total !!}</td>
                <td class="k">{!! $crystal_total !!}</td>
                <td class="k">{!! $deuterium_total !!}</td>
                <td class="k">{!! $energy_total !!}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('game/resources.rs_daily') }}</th>
                <th role="cell">{!! $daily_metal !!}</th>
                <th role="cell">{!! $daily_crystal !!}</th>
                <th role="cell">{!! $daily_deuterium !!}</th>
                <th role="cell">{!! $energy_total !!}</th>
            </tr>
            <tr>
                <th scope="row">{{ __('game/resources.rs_weekly') }}</th>
                <th role="cell">{!! $weekly_metal !!}</th>
                <th role="cell">{!! $weekly_crystal !!}</th>
                <th role="cell">{!! $weekly_deuterium !!}</th>
                <th role="cell">{!! $energy_total !!}</th>
            </tr>
        </tbody>
    </table>
</form>
@endsection