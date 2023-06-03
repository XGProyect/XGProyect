<tr height="20">
    <td class="c" colspan="3">
        {{ __('game/fleet.fl_hold_time') }}
    </td>
</tr>
<tr height="20">
    <th colspan="3">
        <select name="{{ $stay_type }}">
            @foreach ($options as $item)
            <option value="{{ $item['value'] }}"{{ $item['selected'] }}>{{ $item['value'] }}</option>
            @endforeach
        </select>{{ __('game/fleet.fl_hours') }}
    </th>
</tr>
