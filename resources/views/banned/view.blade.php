@extends('master.game')

@section('content')
    <br>
    <div id="content" role="main">
        <table width="600px">
            <tr>
                <td class="c" colspan="5">{{ __('game/banned.bn_players_banned_list') }}</td>
            </tr>
            <tr>
                <th>{{ __('game/banned.bn_player') }}</th>
                <th>{{ __('game/banned.bn_reason') }}</th>
                <th>{{ __('game/banned.bn_from') }}</th>
                <th>{{ __('game/banned.bn_until') }}</th>
                <th>{{ __('game/banned.bn_by') }}</th>
            </tr>
            @foreach ($bannedPlayers as $banned)
            <tr>
                <th scope="row" class="b">{{ $banned['player'] }}</th>
                <th role="cell" class="b">{{ $banned['reason'] }}</th>
                <th role="cell" class="b">{{ $banned['since'] }}</th>
                <th role="cell" class="b">{{ $banned['until'] }}</th>
                <th role="cell" class="b">{!! $banned['by'] !!}</th>
            </tr>
            @endforeach
            <tr>
                <th role="cell" class="5" colspan="5">
                    {{ trans_choice('game/banned.bn_players_banned', $bannedTotal, ['count' => $bannedTotal]) }}
                </th>
            </tr>
        </table>
    </div>
@endsection