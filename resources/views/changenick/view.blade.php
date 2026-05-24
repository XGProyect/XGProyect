@extends('master.game')

@section('content')
<x-notice width="519px" :color="$color" :message="$message" />
<form autocomplete="off" method="post" name="changenick" id="changenick" action="game.php?page=changenick" role="form">
    <input type="hidden" name="mode" value="save" />
    <table role="presentation" width="519px">
        <tbody>
            <tr>
                <td role="heading" aria-level="2" class="c" colspan="2">{{ __('game/changenick.cn_title') }}</td>
            </tr>
            <tr>
                <th>{{ __('game/preferences.pr_your_player_name') }}:</th>
                <th>{{ $currentName }}</th>
            </tr>
            @if ($canChangeName)
            <tr>
                <th>{{ __('game/preferences.pr_new_player_name') }}:</th>
                <th>
                    <input type="text" name="new_user_name" size="30" minlength="3" maxlength="20" autocomplete="off" />
                </th>
            </tr>
            <tr>
                <th>{{ __('game/preferences.pr_enter_password_confirmation') }}:</th>
                <th>
                    <input type="password" name="confirmation_user_password" size="30" minlength="8" autocomplete="off" />
                </th>
            </tr>
            @else
            <tr>
                <th colspan="2" style="color: #ff0000;">
                    {{ __('game/changenick.cn_next_change_at', ['date' => $nextChangeAt]) }}
                </th>
            </tr>
            @endif
            <tr>
                <th colspan="2" style="text-align: justify; font-weight: normal;">
                    {{ __('game/changenick.cn_username_change_message') }}
                </th>
            </tr>
            @if ($canChangeName)
            <tr>
                <th colspan="2">
                    <input type="submit" name="apply_settings" value="{{ __('game/preferences.pr_use_settings') }}" />
                </th>
            </tr>
            @endif
        </tbody>
    </table>
</form>
@endsection