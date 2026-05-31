@extends('master.game')

@section('content')
<table width="520">
    <tr>
        <td class="c">{{ __('game/buddies.bu_buddy_list') }}</td>
    </tr>
    <tr>
        <th>{{ $message }}</th>
    </tr>
    <tr>
        <td class="c">
            <a href="game.php?page=buddies">{{ __('game/buddies.bu_back') }}</a>
        </td>
    </tr>
</table>
@endsection
