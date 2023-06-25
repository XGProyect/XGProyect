@extends('master.game')

@section('content')
<table width="520">
    <tr>
        <td class="c" colspan="5">{{ __('game/buddies.bu_buddy_list') }}</td>
    </tr>
    <tr>
        <td role="columnheader" class="c">{{ __('game/buddies.bu_player') }}</td>
        <td role="columnheader" class="c">{{ __('game/buddies.bu_alliance') }}</td>
        <td role="columnheader" class="c">{{ __('game/buddies.bu_coords') }}</td>
        <td role="columnheader" class="c">{{ __('game/buddies.bu_text') }}</td>
        <td role="columnheader" class="c">{{ __('game/buddies.bu_action') }}</td>
    </tr>
    <tr>
        <th role="cell" class="c" colspan="5">{{ __('game/buddies.bu_requests') }}</a></th>
    </tr>
    @foreach ($list_of_requests_received as $item)
    <tr>
        <th scope="row">
            <a href="game.php?page=chat&playerId={{ $item['id'] }}">{{ $item['username'] }}</a>
        </th>
        <th role="cell">
            <a href="game.php?page=alliance&mode=ainfo&allyid={{ $item['ally_id'] }}">{{ $item['alliance_name'] }}</a>
        </th>
        <th role="cell">
            <a href="game.php?page=galaxy&mode=3&galaxy={{ $item['galaxy'] }}&system={{ $item['system'] }}">{{ $item['galaxy'] }}:{{ $item['system'] }}:{{ $item['planet'] }}</a>
        </th>
        <th role="cell">
            {{ $item['text'] }}
        </th>
        <th role="cell">
            {!! $item['action'] !!}
        </th>
    </tr>
    @endforeach
    <tr>
        <th role="cell" class="c" colspan="5">{{ __('game/buddies.bu_my_requests') }}</th>
    </tr>
    @foreach ($list_of_requests_sent as $item)
    <tr>
        <th scope="row">
            <a href="game.php?page=chat&playerId={{ $item['id'] }}">{{ $item['username'] }}</a>
        </th>
        <th role="cell">
            <a href="game.php?page=alliance&mode=ainfo&allyid={{ $item['ally_id'] }}">{{ $item['alliance_name'] }}</a>
        </th>
        <th role="cell">
            <a href="game.php?page=galaxy&mode=3&galaxy={{ $item['galaxy'] }}&system={{ $item['system'] }}">{{ $item['galaxy'] }}:{{ $item['system'] }}:{{ $item['planet'] }}</a>
        </th>
        <th role="cell">
            {{ $item['text'] }}
        </th>
        <th role="cell">
            {!! $item['action'] !!}
        </th>
    </tr>
    @endforeach
    <tr>
        <th role="cell" class="c" colspan="5">{{ __('game/buddies.bu_partners') }}</a></th>
    </tr>
    @foreach ($list_of_buddies as $item)
    <tr>
        <th scope="row">
            <a href="game.php?page=chat&playerId={{ $item['id'] }}">{{ $item['username'] }}</a>
        </th>
        <th role="cell">
            <a href="game.php?page=alliance&mode=ainfo&allyid={{ $item['ally_id'] }}">{{ $item['alliance_name'] }}</a>
        </th>
        <th role="cell">
            <a href="game.php?page=galaxy&mode=3&galaxy={{ $item['galaxy'] }}&system={{ $item['system'] }}">{{ $item['galaxy'] }}:{{ $item['system'] }}:{{ $item['planet'] }}</a>
        </th>
        <th role="cell">
            {!! $item['text'] !!}
        </th>
        <th role="cell">
            {!! $item['action'] !!}
        </th>
    </tr>
    @endforeach
</table>
@endsection