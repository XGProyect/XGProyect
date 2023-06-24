@extends('master.game')

@section('content')
<script type="text/javascript" src="{{ asset('assets/js/flotten-min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/ocnt-min.js') }}"></script>
<br>
<div id="content" role="main">
    <table width="519" border="0" cellpadding="0" cellspacing="1">
        <tr height="20">
            <td colspan="9" class="c">
                <table border="0" width="100%">
                    <tr>
                        <td style="background-color: transparent;">{{ __('game/fleet.fl_fleets') }} {{ $fleets }} / {{ $max_fleets }} &nbsp; &nbsp; {{ __('game/fleet.fl_expeditions') }} {{ $expeditions }} / {{ $max_expeditions }}</td>
                        <td style="background-color: transparent;" align="right"><a href="game.php?page=movement">{{ __('game/fleet.fl_fleets_movements') }}</a></td>
                    </tr>
                </table>
            </td>
        </tr>
        {!! $no_slot !!}
    </table>
    <form action="game.php?page=fleet2" method="POST" role="form">
        <table width="519" border="0" cellpadding="0" cellspacing="1">
            <tr height="20">
                <td colspan="4" class="c">{{ __('game/fleet.fl_new_mission_title') }}</td>
            </tr>
            <tr height="20">
                <th>{{ __('game/fleet.fl_ship_type') }}</th>
                <th>{{ __('game/fleet.fl_ship_available') }}</th>
                <th>-</th>
                <th>-</th>
            </tr>
            @foreach ($list_of_ships as $item)
            <tr height="20px">
                <th scope="row">
                    {!! $item['ship_name'] !!}
                </th>
                <th role="cell">
                    {{ $item['ship_amount'] }}
                </th>
                <th role="cell">
                    {!! $item['max_ships_link'] !!}
                </th>
                <th role="cell">
                    {!! $item['ships_input'] !!}
                    <input type="hidden" name="maxship{{ $item['ship_id'] }}" value="{{ $item['max_ships'] }}" />
                    <input type="hidden" name="consumption{{ $item['ship_id'] }}" value="{{ $item['consumption'] }}" />
                    <input type="hidden" name="speed{{ $item['ship_id'] }}" value="{{ $item['speed'] }}" />
                    <input type="hidden" name="capacity{{ $item['ship_id'] }}" value="{{ $item['capacity'] }}" />
                </th>
            </tr>
            @endforeach
            </tr>
            {!! $none_max_selector !!}
            {!! $no_ships !!}
            {!! $continue_button !!}
        </table>
        <input type="hidden" name="galaxy" value="{{ $galaxy }}" />
        <input type="hidden" name="system" value="{{ $system }}" />
        <input type="hidden" name="planet" value="{{ $planet }}" />
        <input type="hidden" name="planet_type" value="{{ $planettype }}" />
        <input type="hidden" name="target_mission" value="{{ $target_mission }}" />
    </form>
</div>
@endsection