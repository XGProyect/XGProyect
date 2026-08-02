<tr>
    <td role="columnheader" class="c" width="60px">{{ __('game/highscore.st_position') }}</td>
    <td role="columnheader" class="c"></td>
    <td role="columnheader" class="c">{{ __('game/highscore.st_player') }}</td>
    <td role="columnheader" class="c">{{ __('game/highscore.st_actions') }}</td>
    <td role="columnheader" class="c">{{ __('game/highscore.st_points') }}</td>
</tr>
@foreach ($rows as $row)
    <tr>
        <th role="cell">{{ $row['position'] }}</th>
        <th role="cell">
            @php($delta = $row['rank_change'])
            @if ($delta['delta'] === 0)
                <font color="#87CEEB">*</font>
            @elseif ($delta['delta'] < 0)
                <font color="red">{{ $delta['label'] }}</font>
            @else
                <font color="green">{{ $delta['label'] }}</font>
            @endif
        </th>
        <th role="cell" align="left">
            @if ($row['alliance_name'] !== '')
                <a href="game.php?page=alliance&mode=ainfo&allyid={{ $row['alliance_id'] }}">
                    @if ($row['alliance_is_mine'])
                        <font color="#33CCFF">[{{ $row['alliance_name'] }}]</font>
                    @else
                        [{{ $row['alliance_name'] }}]
                    @endif
                </a>
            @endif
            @if ($row['is_self'])
                <font color="lime">{{ $row['name'] }}</font>
            @else
                {{ $row['name'] }}
            @endif
        </th>
        <th role="cell">
            @if ($row['can_message'])
                <a href="game.php?page=chat&playerId={{ $row['player_id'] }}">
                    <img src="{{ asset('assets/img/m.gif') }}" border="0" title="{{ __('game/global.write_message') }}" />
                </a>
            @endif
        </th>
        <th role="cell" align="right">{{ $row['points'] }}</th>
    </tr>
@endforeach
