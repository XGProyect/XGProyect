@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <a href="game.php?page=alliance&mode=admin&edit=ally">{{ __('game/alliance.al_back') }}</a>

    <table width="519"><tr><td class="c" colspan="11">{{ __('game/alliance.al_configure_ranks') }}</td></tr>
        <form action="game.php?page=alliance&mode=admin&edit=rights" method="POST" role="form">
            <tr>
                <th colspan="2"><span style="color: #6f9fc8;">{{ __('game/alliance.al_rank_name_title') }}</span></th>
                <th style="color: #6f9fc8;" colspan="2">{{ __('game/alliance.al_rank_applications_title') }}</th>
                <th style="color: #6f9fc8;" colspan="4">{{ __('game/alliance.al_rank_member_title') }}</th>
                <th style="color: #6f9fc8;" colspan="3">{{ __('game/alliance.al_rank_alliance_title') }}</th>
            </tr>
            <tr>
                <th colspan="2"></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r3.png') }}" alt="{{ __('game/alliance.al_legend_see_requests') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r5.png') }}" alt="{{ __('game/alliance.al_legend_check_requests') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r4.png') }}" alt="{{ __('game/alliance.al_legend_see_users_list') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r2.png') }}" alt="{{ __('game/alliance.al_legend_kick_users') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r7.png') }}" alt="{{ __('game/alliance.al_legend_see_connected_users') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r8.png') }}" alt="{{ __('game/alliance.al_legend_create_circular') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r1.png') }}" alt="{{ __('game/alliance.al_legend_disolve_alliance') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r6.png') }}" alt="{{ __('game/alliance.al_legend_admin_alliance') }}"/></th>
                <th><img src="{{ asset('upload/skins/xgproyect/img/r9.png') }}" alt="{{ __('game/alliance.al_legend_right_hand') }}"/></th>
            </tr>
            @foreach ($list_of_ranks as $item)
            <tr>
                <th role="cell">{!! $item['rank_delete'] !!}</th>
                <th scope="row">{{ $item['rank_name'] }}</th>
                <input type="hidden" name="id[]" value="{{ $item['rank_id'] }}">
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r3" {{ $item['checked_r3'] }}{{ $item['edit_check'] }}></th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r5" {{ $item['checked_r5'] }}{{ $item['edit_check'] }}></th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r4" {{ $item['checked_r4'] }}{{ $item['edit_check'] }}></th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r2" {{ $item['checked_r2'] }}{{ $item['edit_check'] }}></th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r7" {{ $item['checked_r7'] }}{{ $item['edit_check'] }}></th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r8" {{ $item['checked_r8'] }}{{ $item['edit_check'] }}></th>
                <th role="cell">{!! $item['r1'] !!}</th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r6" {{ $item['checked_r6'] }}{{ $item['edit_check'] }}></th>
                <th role="cell"><input type="checkbox" name="u{{ $item['rank_id'] }}r9" {{ $item['checked_r9'] }}{{ $item['edit_check'] }}></th>
            </tr>
            @endforeach
            <tr>
                <th role="cell" colspan="11"><span style="float:rigth!important;"><input type="submit" value="{{ __('game/alliance.al_save') }}"></span></th>
            </tr>
            <tr>
                <th role="cell" colspan="11" style="text-align:left;">
                    {!! __('game/alliance.al_rank_warning') !!}
                </th>
            </tr>
        </form>
    </table>
    <br>
    <form action="game.php?page=alliance&mode=admin&edit=rights" method="POST" role="form">
        <table width="519">
            <tr>
                <td class="c" colspan="2">{{ __('game/alliance.al_create_new_rank') }}</td>
            </tr>
            <tr>
                <th scope="row">{{ __('game/alliance.al_rank_name') }}</th>
                <th role="cell"><input type="text" name="newrangname" size="20" maxlength="30"></th>
            </tr>
            <tr>
                <th role="cell" colspan="2"><input type="submit" name="create" value="{{ __('game/alliance.al_create') }}"></th>
            </tr>
        </table>
    </form>
    <form action="game.php?page=alliance&mode=admin&edit=rights" method="POST" role="form">
        <table width="519">
            <tr>
                <td class="c" colspan="2">{{ __('game/alliance.al_legend') }}</td>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r1.png') }}" alt="{{ __('game/alliance.al_legend_disolve_alliance') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_disolve_alliance') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r2.png') }}" alt="{{ __('game/alliance.al_legend_kick_users') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_kick_users') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r3.png') }}" alt="{{ __('game/alliance.al_legend_see_requests') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_see_requests') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r4.png') }}" alt="{{ __('game/alliance.al_legend_see_users_list') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_see_users_list') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r5.png') }}" alt="{{ __('game/alliance.al_legend_check_requests') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_check_requests') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r6.png') }}" alt="{{ __('game/alliance.al_legend_admin_alliance') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_admin_alliance') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r7.png') }}" alt="{{ __('game/alliance.al_legend_see_connected_users') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_see_connected_users') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r8.png') }}" alt="{{ __('game/alliance.al_legend_create_circular') }}"/></th>
                <th role="cell">{{ __('game/alliance.al_legend_create_circular') }}</th>
            </tr>
            <tr>
                <th role="cell"><img src="{{ asset('upload/skins/xgproyect/img/r9.png') }}" alt="{{ __('game/alliance.al_legend_right_hand') }}"/></th>
                <th role="cell"><a title="{{ __('game/alliance.al_legend_right_hand_detail') }}">{{ __('game/alliance.al_legend_right_hand') }}</a></th>
            </tr>
        </table>
    </form>
</div>
@endsection