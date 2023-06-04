@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table width="519px">
        <tr>
            <td class="c" colspan="2">{{ __('game/alliance.al_request_list') }}</td>
        </tr>
        @if($showForm)
        <script src="{{ asset('js/cntchar-min.js') }}" type="text/javascript"></script>
        <form action="game.php?page=alliance&mode=admin&edit=requests&show={{ $id }}&sort=0" method="POST" role="form">
            <tr>
                <th role="cell" colspan="2">{{ $request_from }}</th>
            </tr>
            <tr>
                <th role="cell" colspan="2">{{ $request_text }}</th>
            </tr>
            <tr>
                <td class="c" colspan="2">{{ __('game/alliance.al_reply_to_request') }}</td>
            </tr>
            <tr>
                <th scope="row">
                    {{ __('game/alliance.al_reason') }} <span id="cntChars">0</span> / 500 {{ __('game/alliance.al_characters') }}
                </th>
                <th role="cell">
                    <textarea name="text" cols="40" rows="10" onkeyup="javascript:cntchar(500)"></textarea>
                </th>
            </tr>
            <tr>
                <th role="cell" colspan="2">
                    <input type="submit" name="accept" value="{{ __('game/alliance.al_acept_request') }}"/>
                    <input type="submit" name="cancel" value="{{ __('game/alliance.al_decline_request') }}"/>
                </th>
            </tr>
        </form>
        @endif
        <tr>
            <th role="cell" colspan="2">{{ $pending_message }}</th>
        </tr>
        <tr>
            <td role="columnheader" class="c"><a href="game.php?page=alliance&mode=admin&edit=requests&show=0&sort=1">{{ __('game/alliance.al_candidate') }}</a></td>
            <td role="columnheader" class="c"><a href="game.php?page=alliance&mode=admin&edit=requests&show=0&sort=0">{{ __('game/alliance.al_request_date') }}</a></th>
        </tr>
        @foreach ($requestsList as $item)
        <tr>
            <th scope="row">
                <a href="game.php?page=alliance&mode=admin&edit=requests&show={{ $item['id'] }}&sort=0">{{ $item['username'] }}</a>
            </th>
            <th role="cell">{{ $item['time'] }}</th>
        </tr>
        @endforeach
        @if ($noRequests)
        <tr>
            <th role="cell" colspan="2">
                {{ __('game/alliance.al_no_requests') }}
            </th>
        </tr>
        @endif
        <tr>
            <td class="c" colspan="2"><a href="game.php?page=alliance">{{ __('game/alliance.al_back') }}</a></td>
        </tr>
    </table>
</div>
@endsection