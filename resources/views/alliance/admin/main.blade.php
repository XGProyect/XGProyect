@extends('master.game')

@section('content')
<script type="text/javascript" src="{{ asset('js/cntchar-min.js') }}"></script>
<br>
<div id="content" role="main">
    <table role="presentation" width="519px">
        <tr>
            <td role="heading" aria-level="2" class="c" colspan="2">{{ __('game/alliance.al_manage_alliance') }}</td>
        </tr>
        <tr>
            <th colspan="2"><a href="game.php?page=alliance&mode=admin&edit=rights">{{ __('game/alliance.al_manage_ranks') }}</a></th>
        </tr>
        <tr>
            <th colspan="2"><a href="game.php?page=alliance&mode=admin&edit=members">{{ __('game/alliance.al_manage_members') }}</a></th>
        </tr>
        <tr>
            <th colspan="2">
                <a href="game.php?page=alliance&mode=admin&edit=tag">
                <img src="{{ asset('upload/skins/xgproyect/alliance/appwiz.gif') }}" border="0" alt="{{ __('game/alliance.al_manage_change_tag') }}"/>
                </a>
                &nbsp;
                <a href="game.php?page=alliance&mode=admin&edit=name">
                    <img src="{{ asset('upload/skins/xgproyect/alliance/appwiz.gif') }}" border="0" alt="{{ __('game/alliance.al_manage_change_name') }}"/>
                </a>
            </th>
        </tr>
    </table>
    <form action="" method="POST" role="form">
        <input type="hidden" name="t" value="{t}">
        <table role="presentation" width="519">
            <tr>
                <td role="heading" aria-level="2" class="c" colspan="3">{{ __('game/alliance.al_texts') }}</td>
            </tr>
            <tr>
                <th><a href="game.php?page=alliance&mode=admin&edit=ally&t=1">{{ __('game/alliance.al_outside_text') }}</a></th>
                <th><a href="game.php?page=alliance&mode=admin&edit=ally&t=2">{{ __('game/alliance.al_inside_text') }}</a></th>
                <th><a href="game.php?page=alliance&mode=admin&edit=ally&t=3">{{ __('game/alliance.al_request_text') }}</a></th>
            </tr>
            <tr>
                <td class="c" colspan="3">{{ __('game/alliance.al_message') }} (<span id="cntChars">0</span> / 5000 {{ __('game/alliance.al_characters') }})</td>
            </tr>
            <tr>
                <th colspan="3">
                    <textarea name="text" cols="70" rows="15" onkeyup="javascript:cntchar(5000)">{{ $text }}</textarea>
                    {{ $request_type }}
                </th>
            </tr>
            <tr>
                <th colspan="3">
                    <input type="hidden" name="t" value="{t}"><input type="reset" value="{{ __('game/alliance.al_circular_reset') }}">
                    <input type="submit" value="{{ __('game/alliance.al_save') }}">
                </th>
            </tr>
        </table>
    </form>
    <form action="" method="POST" role="form">
        <table width=519>
            <tr>
                <td class="c" colspan="2">{{ __('game/alliance.al_manage_options') }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_web_site') }}</th>
                <th role="cell"><input type="text" name="web" value="{{ $alliance_web }}" size="70"></th>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_manage_image') }}</th>
                <th role="cell"><input type="text" name="image" value="{{ $alliance_image }}" size="70"></th>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_manage_requests') }}</th>
                <th role="cell">
                    <select name="request_notallow">
                        <option value="0" {{ $alliance_request_notallow_0 }}>{{ __('game/alliance.al_requests_not_allowed') }}</option>
                        <option value="1" {{ $alliance_request_notallow_1 }}>{{ __('game/alliance.al_requests_allowed') }}</option>
                    </select>
                </th>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_manage_founder_rank') }}</th>
                <th role="cell">
                    <input type="text" name="owner_range" value="{{ $alliance_owner_range }}" size=30>
                </th>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_manage_newcomer_rank') }}</th>
                <th role="cell">
                    <input type="text" name="newcomer_range" value="{{ $alliance_newcomer_range }}" size=30>
                </th>
            </tr>
            <tr>
                <th role="cell" colspan="2">
                    <input type="submit" name="options" value="{{ __('game/alliance.al_save') }}">
                </th>
            </tr>
        </table>
    </form>
    <table width="519" role="presentation">
        <tr>
            <td role="heading" aria-level="2" class="c">{{ __('game/alliance.al_disolve_alliance') }}</td>
        </tr>
        <tr>
            <th><input type="button" onclick="javascript:location.href = 'game.php?page=alliance&mode=admin&edit=exit';" value="{{ __('game/alliance.al_continue') }}"/></th>
        </tr>
    </table>
    <table width="519">
        <tr>
            <td role="heading" aria-level="2" class="c">{{ __('game/alliance.al_transfer_alliance') }}</td>
        </tr>
        <tr>
            <th><input type="button" onclick="javascript:location.href = 'game.php?page=alliance&mode=admin&edit=transfer';" value="{{ __('game/alliance.al_continue') }}"/></th>
        </tr>
    </table>
</div>
@endsection