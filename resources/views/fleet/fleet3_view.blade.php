@extends('master.game')

@section('content')
<script type="text/javascript" src="{{ asset('js/flotten-min.js') }}"></script>
<script type="text/javascript">
    function getStorageFaktor() {
        return 1
    }
</script>
<form action="game.php?page=fleet4" method="post" onsubmit='this.submit.disabled = true;' role="form">
    <input type="hidden" name="thisresource1"  value="{{ $this_metal }}" />
    <input type="hidden" name="thisresource2"  value="{{ $this_crystal }}" />
    <input type="hidden" name="thisresource3"  value="{{ $this_deuterium }}" />
    <input type="hidden" name="thisgalaxy"     value="{{ $this_galaxy }}" />
    <input type="hidden" name="thissystem"     value="{{ $this_system }}" />
    <input type="hidden" name="thisplanet"     value="{{ $this_planet }}" />
    <input type="hidden" name="thisplanettype" value="{{ $this_planet_type }}" />
    <input type="hidden" name="galaxy"         value="{{ $galaxy_end }}" />
    <input type="hidden" name="system"         value="{{ $system_end }}" />
    <input type="hidden" name="planet"         value="{{ $planet_end }}" />
    <input type="hidden" name="planettype"     value="{{ $planet_type_end }}" />
    <input type="hidden" name="speed"          value="{{ $speed }}" />
    <input type="hidden" name="speedfactor"    value="{{ $speedfactor }}" />
    @foreach ($fleet_block as $item)
        <input type="hidden" name="consumption{{ $item['ship_id'] }}" value="{{ $item['consumption'] }}" />
        <input type="hidden" name="speed{{ $item['ship_id'] }}" value="{{ $item['speed'] }}" />
        <input type="hidden" name="capacity{{ $item['ship_id'] }}" value="{{ $item['capacity'] }}" />
        <input type="hidden" name="ship{{ $item['ship_id'] }}" value="{{ $item['ship'] }}" />
    @endforeach
    <br>
    <div id="content" role="main">
        <table role="presentation" border="0" cellpadding="0" cellspacing="1" width="519">
            <tr align="left" height="20">
                <td class="c" colspan="2">{!! $title !!}</td>
            </tr>
            <tr align="left" valign="top">
                <th width="50%">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="259">
                        <tr height="20">
                            <td class="c" colspan="2">{{ __('game/fleet.fl_mission') }}</td>
                        </tr>
                        @foreach ($mission_selector as $item)
                        <tr height="20">
                            <th>
                                <input id="{{ $item['id'] }}" type="radio" name="mission" value="{{ $item['value'] }}"{!! $item['checked'] !!}/>
                                <label for="{{ $item['id'] }}">{{ $item['mission'] }}</label>
                                <br>
                                {{ $item['expedition_message'] }}
                            </th>
                        </tr>
                        @endforeach
                    </table>
                </th>
                <th>
                    <table border="0" cellpadding="0" cellspacing="0" width="259">
                        <tr height="20">
                            <td colspan="3" class="c">{{ __('game/fleet.fl_resources') }}</td>
                        </tr>
                        <tr height="20">
                            <th scope="row">{{ __('game/global.metal') }}</th>
                            <th role="cell"><a href="javascript:maxResource('1');">{{ __('game/fleet.fl_max') }}</a></th>
                            <th role="cell"><input name="resource1" size="10" onchange="calculateTransportCapacity();" type="text"></th>
                        </tr>
                        <tr height="20">
                            <th scope="row">{{ __('game/global.crystal') }}</th>
                            <th role="cell"><a href="javascript:maxResource('2');">{{ __('game/fleet.fl_max') }}</a></th>
                            <th role="cell"><input name="resource2" size="10" onchange="calculateTransportCapacity();" type="text"></th>
                        </tr>
                        <tr height="20">
                            <th scope="row">{{ __('game/global.deuterium') }}</th>
                            <th role="cell"><a href="javascript:maxResource('3');">{{ __('game/fleet.fl_max') }}</a></th>
                            <th role="cell"><input name="resource3" size="10" onchange="calculateTransportCapacity();" type="text"></th>
                        </tr>
                        <tr height="20">
                            <th scope="row">{{ __('game/fleet.fl_resources_left') }}</th>
                            <th role="cell" colspan="2"><div id="remainingresources">-</div></th>
                        </tr>
                        <tr height="20">
                            <th role="cell" colspan="3"><a href="javascript:maxResources()">{{ __('game/fleet.fl_all_resources') }}</a></th>
                        </tr>
                        <tr height="20">
                            <th role="cell" colspan="3">&nbsp;</th>
                        </tr>
                        {!! $stay_block !!}
                    </table>
                </th>
            </tr>
            <tr height="20">
                <th colspan="2"><input value="{{ __('game/fleet.fl_continue') }}" type="submit" name="submit"></th>
            </tr>
        </table>
    </div>
</form>
@endsection