@if ($color && $message)
<table role="presentation" width="{{ $width }}" style="border: 3px solid {{ $color }}; text-align: center; background: transparent;">
    <tr style="background: transparent;">
        <td style="background: transparent;">
            {{ $message }}
        </td>
    </tr>
</table>
<br />
@endif
