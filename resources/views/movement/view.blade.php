@extends('master.game')

@section('content')
<script type="text/javascript" src="{{ asset('js/flotten-min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/ocnt-min.js') }}"></script>
<br>
<div id="content" role="main">
    <table width="519" border="0" cellpadding="0" cellspacing="1">
        <tr height="20">
            <td colspan="9" class="c">
                <table border="0" width="100%">
                    <tr>
                        <td style="background-color: transparent;" align="center">
                            {{ __('game/fleet.fl_fleets') }} {{ $fleets }} / {{ $max_fleets }} &nbsp; &nbsp; {{ __('game/fleet.fl_expeditions') }} {{ $expeditions }} / {{ $max_expeditions }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr height="20">
            <th>{{ __('game/fleet.fl_number') }}</th>
            <th>{{ __('game/fleet.fl_mission') }}</th>
            <th>{{ __('game/fleet.fl_ammount') }}</th>
            <th>{{ __('game/fleet.fl_beginning') }}</th>
            <th>{{ __('game/fleet.fl_departure') }}</th>
            <th>{{ __('game/fleet.fl_destiny') }}</th>
            <th>{{ __('game/fleet.fl_objective') }}</th>
            <th>{{ __('game/fleet.fl_arrival') }}</th>
            <th>{{ __('game/fleet.fl_order') }}</th>
        </tr>
        @foreach ($list_of_movements as $item)
        <tr height="20px">
            <th scope="row">{{ $item['num'] }}</th>
            <th role="cell">
                <a>{{ $item['fleet_mission'] }}</a>
                <a title="{{ $item['tooltip'] }}">{{ $item['title'] }}</a>
            </th>
            <th role="cell">
                <a title="{{ $item['fleet'] }}">{{ $item['fleet_amount'] }}</a>
            </th>
            <th role="cell">
                {!! $item['fleet_start'] !!}
            </th>
            <th role="cell">
                {{ $item['fleet_start_time'] }}
            </th>
            <th role="cell">
                {!! $item['fleet_end'] !!}
            </th>
            <th role="cell">
                {{ $item['fleet_end_time'] }}
            </th>
            <th role="cell">
                {{ $item['fleet_arrival'] }}
            </th>
            <th role="cell" style="vertical-align: middle">
                {!! $item['fleet_actions'] !!}
            </th>
        </tr>
        @endforeach
    </table>
</div>
@endsection