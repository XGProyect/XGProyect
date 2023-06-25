@extends('master.game')

@section('content')
<script src="{{ asset('assets/js/cntchar-min.js') }}" type="text/javascript"></script>
<x-notice width="519px" :color="$error_color" :message="$error_text" />
<form action="game.php?page=chat&playerId={{ $id }}" method="post" role="form">
    <table width="519px">
        <tr>
            <td class="c" colspan="2">{{ __('game/chat.pm_send_message') }}</td>
        </tr>
        <tr>
            <th scope="row">{{ __('game/chat.pm_to') }}</th>
            <th role="cell">{!! $to !!}</th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/chat.pm_subject') }}</th>
            <th role="cell"><input type="text" name="subject" size="40" maxlength="40" value="{{ $subject }}" /></th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/chat.pm_message') }} (<span id="cntChars">0</span> / 5000 {{ __('game/chat.pm_chars') }})</th>
            <th role="cell">
                <textarea name="text" cols="40" rows="10" size="100" onkeyup="javascript:cntchar(5000)">{{ $text }}</textarea>
            </th>
        </tr>
        <tr>
            <th role="cell" colspan="2">
                <input type="submit" value="{{ __('game/chat.pm_send') }}" />
            </th>
        </tr>
    </table>
</form>
@endsection