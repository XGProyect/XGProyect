@extends('master.game')

@section('content')
<table width="519">
    <tr>
        <td class="c" colspan="1">
            {{ __('game/menu.lm_playerprofile') }}
        </td>
    </tr>
    <tr>
        <th scope="row">
            @if ($alliance !== null)
                <a href="game.php?page=alliance&mode=ainfo&allyid={{ $alliance['id'] }}"><font color="#33CCFF">[{{ $alliance['tag'] }}]</font></a>
            @endif
            <a href="game.php?page=changenick" title="{{ __('game/changenick.cn_title') }}" onclick="f('game.php?page=changenick', 'changenick'); return false;">{{ $playerName }}</a>
        </th>
    </tr>
</table>
@endsection