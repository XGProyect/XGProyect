@extends('master.game')

@section('content')
<form action="" method="POST" role="form">
    <table width="519">
        <tr>
            <td class="c" colspan="2">{{ $case }}</td>
        </tr>
        <tr>
            <th scope="row">{{ $title }}</th>
            <th role="cell">
                <input type="text" name="nametag" maxlength="30">
                <input type="submit" value="{{ __('game/alliance.al_change_submit') }}"></th>
        </tr>
        <tr>
            <td class="c" colspan="2">
                <a href="game.php?page=alliance&mode=admin&edit=ally">{{ __('game/alliance.al_back') }}</a>
            </td>
        </tr>
    </table>
</form>
@endsection