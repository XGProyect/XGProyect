@extends('master.game', ['noTopnav' => true, 'noLeftMenu' => true])

@section('content')
<script src="{{ asset('assets/js/cntchar-min.js') }}" type="text/javascript"></script>
<form action="game.php?page=notices" method="POST">
    <input type="hidden" name="s" value="{{ $formAction }}">
    @if($noteId)
    <input type="hidden" name="n" value="{{ $noteId }}">
    @endif
    <table width="519px">
        <tr>
            <td class="c" colspan="2">{{ $title }}</td>
        </tr>
        <tr>
            <th>{{ __('game/notices.nt_your_subject') }}:</th>
            <th>
                <input type="text" name="title" size="30" maxlength="30" value="{{ $subject }}">
            </th>
        </tr>
        <tr>
            <th>{{ __('game/notices.nt_priority') }}:</th>
            <th>
                <select name="u">
                    <option value="2" @selected($priority == 2)>{{ __('game/notices.nt_important') }}</option>
                    <option value="1" @selected($priority == 1)>{{ __('game/notices.nt_normal') }}</option>
                    <option value="0" @selected($priority == 0)>{{ __('game/notices.nt_unimportant') }}</option>
                </select>
            </th>
        </tr>
        <tr>
            <th>{{ __('game/notices.nt_your_message') }}:</th>
            <td>
                <textarea name="text" cols="60" rows="10" onkeyup="javascript:cntchar(5000)">{{ $text }}</textarea>
                (<span id="cntChars">0</span> / 5000 {{ __('game/notices.nt_characters') }})
            </td>
        </tr>
        <tr>
            <td class="c">
                <a href="game.php?page=notices">{{ __('game/notices.nt_back') }}</a>
            </td>
            <td class="c">
                <input type="submit" value="{{ __('game/notices.nt_save') }}">
            </td>
        </tr>
    </table>
</form>
@endsection
