@extends('master.game')

@section('content')
<script type="text/javascript" src="{{ asset('assets/js/cntchar-min.js') }}"></script>
<br>
<div id="content" role="main">
    <form action="game.php?page=alliance&mode=circular&sendmail=1" method="POST" role="form">
        <table width="530">
            <tr>
                <td class="c" colspan="2">{{ __('game/alliance.al_circular_send_ciruclar') }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_receiver') }}</th>
                <th role="cell">
                    <select name="r">
                        <option value="0">{{ __('game/alliance.al_all_players') }}</option>
                        @foreach ($ranks_list as $item)
                        <option value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                </th>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_message') }} (<span id="cntChars">0</span> / 5000 {{ __('game/alliance.al_characters') }})</th>
                <th role="cell">
                    <textarea name="text" cols="60" rows="10" onkeyup="javascript:cntchar(5000)"></textarea>
                </th>
            </tr>
            <tr>
                <td class="c"><a href="game.php?page=alliance">{{ __('game/alliance.al_back') }}</a></td>
                <td align="center" class="c">
                    <input type="reset" value="{{ __('game/alliance.al_circular_reset') }}">
                    <input type="submit" value="{{ __('game/alliance.al_circular_send_submit') }}">
                </td>
            </tr>
        </table>
    </form>
</div>
@endsection