@extends('master.game')

@section('content')
<table width="519" role="presentation">
    <tr>
        <td role="heading" aria-level="2" class="c" colspan="2">{{ __('game/alliance.al_alliance') }}</td>
    </tr>
    <tr>
        <th><a href="game.php?page=alliance&mode=make">{{ __('game/alliance.al_alliance_make') }}</a></th>
        <th><a href="game.php?page=alliance&mode=search">{{ __('game/alliance.al_alliance_search') }}</a></th>
    </tr>
</table>
@endsection