@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table width="520px">
        <tr>
            <td class="c" colspan="3">{{ __('game/alliance.al_transfer_alliance') }}</td>
        </tr>
        <form action="game.php?page=alliance&mode=admin&edit=transfer" method="POST" role="form">
            <tr>
                <th scope="row">{{ __('game/alliance.al_transfer_to') }}:</th>
                <th role="cell">
                    <select name="newleader">
                        @foreach ($members as $member)
                        <option value="{{ $member['user_id'] }}">{{ $member['user_name'] }} [{{ $member['user_rank'] }}]</option>
                        @endforeach
                    </select>
                </th>
                <th role="cell">
                    <input type="submit" value="{{ __('game/alliance.al_transfer_submit') }}">
                </th>
            </tr>
        </form>
        <tr>
            <td class="c" colspan="3"><a href="game.php?page=alliance&mode=admin&edit=ally">{{ __('game/alliance.al_back') }}</a></td>
        </tr>
    </table>
</div>
@endsection