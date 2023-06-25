@extends('master.game')

@section('content')
<form name="short_panel" method="POST"  action="game.php?page=shortcuts{{ $shortcut_id }}" role="form">
    <table border="0" cellpadding="0" cellspacing="1" width="519px">
        <tr height="20">
            <td colspan="2" class="c">{{ __('game/fleet.fl_shortcut_add_title') }}</td>
        </tr>
        <tr height="20">
            <th>
                <input type="text" name="name" value="{{ $name }}" size="32" maxlength="32" title="{{ __('game/fleet.fl_shortcut_name') }}">
                <input type="text" name="galaxy" value="{{ $galaxy }}" size="3"  maxlength="1"  title="{{ __('game/fleet.fl_shortcut_galaxy') }}">
                <input type="text" name="system" value="{{ $system }}" size="3"  maxlength="3"  title="{{ __('game/fleet.fl_shortcut_solar_system') }}">
                <input type="text" name="planet" value="{{ $planet }}" size="3"  maxlength="3"  title="{{ __('game/fleet.fl_planet') }}">
                <select name="type">
                    @foreach ($planetTypes as $item)
                    <option {{ $item['selected'] }} value="{{ $item['value'] }}">{{ $item['name'] }}</option>
                    @endforeach
                </select>
            </th>
        </tr>
        <tr>
            <th>
                <input type="button" value="{{ __('game/fleet.fl_register_shorcut') }}" onclick="document.short_panel.action += '&mode={{ $mode }}';submit();" >
                <input type="{{ $visibility }}" value="{{ __('game/fleet.fl_dlte_shortcut') }}" onclick="document.short_panel.action += '&mode=delete';submit();">
            </th>
        </tr>
        <tr>
            <td colspan="2" class="c">
                <a href="game.php?page=shortcuts">{{ __('game/fleet.fl_shortcuts') }}</a>
            </td>
        </tr>
    </table>
</form>
@endsection