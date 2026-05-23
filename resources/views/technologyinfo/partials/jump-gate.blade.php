{!! $jumpGate['countdownScriptHead'] !!}
<form action="{{ $jumpGate['action'] }}" method="post" role="form">
    @csrf
    <table border="0" style="margin: 8px auto 0;">
        <tr>
            <th>{{ __('game/infos.in_jump_gate_start_moon') }}</th>
            <th>{!! $jumpGate['startMoonLink'] !!}</th>
        </tr>
        <tr>
            <th>{{ __('game/infos.in_jump_gate_finish_moon') }}</th>
            <th>
                <select name="jmpto">
                    @foreach ($jumpGate['destinations'] as $destination)
                    <option value="{{ $destination['id'] }}" @selected((int) old('jmpto') === $destination['id'])>{{ $destination['label'] }}</option>
                    @endforeach
                </select>
            </th>
        </tr>
    </table>
    <table width="519" style="margin: 0 auto;">
        <tr>
            <td class="c" colspan="2">{{ __('game/infos.in_jump_gate_select_ships') }}</td>
        </tr>
        <tr>
            <th class="l" colspan="2" align="right">
                <table width="100%">
                    <tr>
                        <td style="background-color: transparent;" align="right">{!! $jumpGate['waitTimeHtml'] !!}</td>
                    </tr>
                </table>
            </th>
        </tr>
        @foreach ($jumpGate['ships'] as $ship)
        <tr>
            <th>
                <a href="game.php?page=technologydetails&technology={{ $ship['id'] }}">{{ $ship['name'] }}</a>
                ({{ $ship['max'] }} {{ $ship['availability'] }})
            </th>
            <th>
                <input tabindex="{{ $ship['tabIndex'] }}" name="c{{ $ship['id'] }}" size="13" maxlength="13" value="{{ old('c' . $ship['id'], '0') }}" type="text">
            </th>
        </tr>
        @endforeach
        <tr>
            <th colspan="2"><input value="{{ __('game/infos.in_jump_gate_jump') }}" type="submit"></th>
        </tr>
        {!! $jumpGate['countdownScriptTail'] !!}
    </table>
</form>