<tr>
    <td role="columnheader" class="c" width="60">{{ __('game/highscore.st_position') }}</td>
    <td role="columnheader" class="c" width="60">&nbsp;</td>
    <td role="columnheader" class="c">{{ __('game/highscore.st_alliance') }}</td>
    <td role="columnheader" class="c">{{ __('game/highscore.st_actions') }}</td>
    <td role="columnheader" class="c">{{ __('game/highscore.st_members') }}</td>
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
        <th role="cell">
            <a href="game.php?page=alliance&mode=ainfo&allyid={{ $row['alliance_id'] }}" target="_blank">
                {{ $row['alliance_name'] }}
            </a>
        </th>
        <th role="cell">
            @if ($row['can_request'])
                <a href="game.php?page=alliance&mode=apply&allyid={{ $row['alliance_id'] }}">
                    <img src="{{ asset('assets/img/m.gif') }}" border="0" title="{{ __('game/highscore.st_ally_request') }}" />
                </a>
            @endif
        </th>
        <th role="cell">{{ $row['members'] }}</th>
        <td align="right" class="b">
            <span style="font-weight:bold;">{{ $row['points'] }}</span>
            <br>
            <span style="font-size:8px;">ø {{ $row['points_per_member'] }}</span>
        </td>
    </tr>
@endforeach
