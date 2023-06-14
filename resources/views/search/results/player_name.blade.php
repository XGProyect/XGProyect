<table width="519px">
    <tr>
        <td role="columnheader" class="c">{{ __('game/search.sh_col_players_name') }}</td>
        <td role="columnheader" class="c">{{ __('game/search.sh_col_alliance') }}</td>
        <td role="columnheader" class="c">{{ __('game/search.sh_col_homeworld') }}</td>
        <td role="columnheader" class="c">{{ __('game/search.sh_col_position') }}</td>
        <td role="columnheader" class="c">{{ __('game/search.sh_col_highscore_ranking') }}</td>
        <td role="columnheader" class="c">{{ __('game/search.sh_col_action') }}</td>
    </tr>
    @foreach ($results as $result)
    <tr>
        <th scope="row">{{ $result['name'] }}</th>
        <th role="cell">
            <a href="game.php?page=alliance&mode=ainfo&id={{ $result['alliance_id'] }}">
                {{ $result['alliance_name'] }}
            </a>
        </th>
        <th role="cell">{{ $result['planet_name'] }}</th>
        <th role="cell">
            <a href="game.php?page=galaxy&mode=3&galaxy={{ $result['planet_galaxy'] }}&system={{ $result['planet_system'] }}">
                {!! $result['planet_position'] !!}
            </a>
        </th>
        <th role="cell">{!! $result['user_rank'] !!}</th>
        <th role="cell">{!! $result['user_actions'] !!}</th>
    </tr>
    @endforeach
</table>