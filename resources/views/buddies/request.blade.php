@extends('master.game')

@section('content')
<script src="{{ asset('js/cntchar-min.js') }}" type="text/javascript"></script>
<br>
<div id="content" role="main">
    <form action="game.php?page=buddies&mode=1&sm=3" method="post" role="form">
        <input type="hidden" name="user" value="{{ $id }}">
        <table width="520">
            <tr>
                <td class="c" colspan="2">{{ __('game/buddies.bu_request_message') }}</td>
            </tr>
            <tr>
                <th>{{ __('game/buddies.bu_player') }}</th>
                <th>{{ $name }}</th>
            </tr>
            <tr>
                <th scope="row">
                    {{ __('game/buddies.bu_request_text') }} (<span id="cntChars">0</span> / 5000 {{ __('game/buddies.bu_characters') }})
                </th>
                <th role="cell">
                    <textarea name="text" cols="60" rows="10" onKeyUp="javascript:cntchar(5000)"></textarea>
                </th>
            </tr>
            <tr>
                <td class="c">
                    <a href="javascript:window.history.back();">{{ __('game/buddies.bu_back') }}</a>
                </td>
                <td class="c">
                    <input type="submit" value="{{ __('game/buddies.bu_send') }}">
                </td>
            </tr>
        </table>
    </form>
</div>
@endsection