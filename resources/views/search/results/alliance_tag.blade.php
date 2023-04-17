<table width="519px">
        <tr>
            <td role="columnheader" class="c">{{ __('game/search.sh_col_tag') }}</td>
            <td role="columnheader" class="c"></td>
            <td role="columnheader" class="c">{{ __('game/search.sh_col_member') }}</td>
            <td role="columnheader" class="c">{{ __('game/search.sh_col_points') }}</td>
            <td role="columnheader" class="c">{{ __('game/search.sh_col_action') }}</td>
        </tr>
        @foreach ($results as $result)
        <tr>
            <th scope="row">
                <a href="game.php?page=alliance&mode=ainfo&allyid={{ $result['alliance_id'] }}">{{ $result['alliance_tag'] }}</a>
            </th>
            <th role="cell">
                <a href="game.php?page=alliance&mode=ainfo&allyid={{ $result['alliance_id'] }}">{{ $result['alliance_name'] }}</a>
            </th>
            <th role="cell">{{ $result['alliance_members'] }}</th>
            <th role="cell">
                <a href="game.php?page=statistics&range=1">{{ $result['alliance_points'] }}</a>
            </th>
            <th role="cell">{{ $result['alliance_actions'] }}</th>
        </tr>
        @endforeach
    </table>