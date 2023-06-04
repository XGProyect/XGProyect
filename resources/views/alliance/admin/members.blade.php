@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table width="600px">
        <tr>
            <td class="c" colspan="9">
                {{ __('game/alliance.al_user_list') }} ({{ __('game/alliance.al_number_of_records') }}: {{ $total }})
            </td>
        </tr>
        <tr>
            <th>{{ __('game/alliance.al_num') }}</th>
            <th><a href="game.php?page=alliance&mode=admin&edit=members&sort1=1&sort2={{ $s }}">{{ __('game/alliance.al_member') }}</a></th>
            <th>{{ __('game/alliance.al_message') }}</th>
            <th><a href="game.php?page=alliance&mode=admin&edit=members&sort1=2&sort2={{ $s }}">{{ __('game/alliance.al_position') }}</a></th>
            <th><a href="game.php?page=alliance&mode=admin&edit=members&sort1=3&sort2={{ $s }}">{{ __('game/alliance.al_points') }}</a></th>
            <th><a href="game.php?page=alliance&mode=admin&edit=members&sort1=0&sort2={{ $s }}">{{ __('game/alliance.al_coords') }}</a></th>
            <th><a href="game.php?page=alliance&mode=admin&edit=members&sort1=4&sort2={{ $s }}">{{ __('game/alliance.al_member_since') }}</a></th>
            <th><a href="game.php?page=alliance&mode=admin&edit=members&sort1=5&sort2={{ $s }}">{{ __('game/alliance.al_estate') }}</a></th>
            <th>{{ __('game/alliance.al_actions') }}</th>
        </tr>
        @foreach ($list_of_members as $item)
        <tr>
            <th scope="row">{{ $item['position'] }}</th>
            <th role="cell">{{ $item['user_name'] }}</th>
            <th role="cell">
                <a href="game.php?page=chat&playerId={{ $item['user_id'] }}">
                    <img src="{{ asset('upload/skins/xgproyect/img/m.gif') }}" border="0" title="{{ $item['write_message'] }}" alt="{{ $item['write_message'] }}"/>
                </a>
            </th>
            <th role="cell">{!! $item['user_ally_range'] !!}</th>
            <th role="cell">{{ $item['points'] }}</th>
            <th role="cell">{!! $item['coords'] !!}</th>
            <th role="cell">{{ $item['user_ally_register_time'] }}</th>
            <th role="cell">{!! $item['online_time'] !!}</th>
            <th role="cell">{!! $item['actions'] !!}</th>
        </tr>
        @endforeach
        <tr>
            <td class="c" colspan="9">
                <a href="game.php?page=alliance&mode=admin&edit=ally">{{ __('game/alliance.al_back') }}</a>
            </td>
        </tr>
    </table>
</div>
@endsection