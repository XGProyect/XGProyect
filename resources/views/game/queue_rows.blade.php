@foreach ($rows as $row)
<tr>
    <td class="l" colspan="2">{{ $row['label'] }}</td>
    <td class="k">
        @if ($row['is_active'])
        <div id="blc" class="z">{{ $row['time_left'] }}<br>
            <a href="{{ $row['cancel_url'] }}">{{ $row['cancel_label'] }}</a>
        </div>
        <script language="JavaScript">
@foreach ($row['timer_variables'] as $name => $value)
{{ $name }} = @json((string) $value);
@endforeach
t();
        </script>
        <strong><font color="lime">{{ $row['finish_at'] }}</font></strong>
        @else
        <font color="red"><a href="{{ $row['remove_url'] }}">{{ $row['remove_label'] }}</a></font>
        @endif
    </td>
</tr>
@endforeach