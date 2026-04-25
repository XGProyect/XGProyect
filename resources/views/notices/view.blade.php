@extends('master.game', ['noTopnav' => true, 'noLeftMenu' => true])

@section('content')
<form action="" method="POST" role="form">
    <table width="519px">
        <tr>
            <td class="c" colspan="4">{{ __('game/notices.nt_my_notes') }}</td>
        </tr>
        <tr>
            <th role="cell" colspan="4">
                <a href="game.php?page=notices&a=1">{{ __('game/notices.nt_new_note') }}</a>
            </th>
        </tr>
        <tr>
            <td role="columnheader" class="c">&nbsp;</td>
            <td role="columnheader" class="c">{{ __('game/notices.nt_col_subject') }}</td>
            <td role="columnheader" class="c">{{ __('game/notices.nt_col_date') }}</td>
        </tr>
        @forelse($notes as $note)
        <tr>
            <th role="cell" width="20">
                <input name="delnote[{{ $note['id'] }}]" value="y" type="checkbox">
            </th>
            <th role="cell">
                <a href="game.php?page=notices&a=2&n={{ $note['id'] }}">
                    <font color="{{ $note['color'] }}">{{ $note['title'] }}</font>
                </a>
            </th>
            <th role="cell" width="150">{{ $note['time'] }}</th>
        </tr>
        @empty
        <tr>
            <th colspan="4">{{ __('game/notices.nt_no_notes_found') }}</th>
        </tr>
        @endforelse
        <tr>
            <td colspan="4">
                <input value="{{ __('game/notices.nt_delete_market_notes') }}" type="submit">
            </td>
        </tr>
    </table>
</form>
@endsection