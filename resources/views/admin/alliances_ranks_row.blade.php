<input type="hidden" name="id[]" value="{{ $i }}">
<tr>
    <td class="text-center">
        <input type="checkbox" class="form-check-input" name="delete_message[]" value="{{ $i }}">
    </td>
    <td class="text-center">{{ $name }}</td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r1"{{ $delete }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r2"{{ $kick }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r3"{{ $bewerbungen }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r4"{{ $memberlist }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r5"{{ $bewerbungenbearbeiten }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r6"{{ $administrieren }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r7"{{ $onlinestatus }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r8"{{ $mails }}></td>
    <td class="text-center"><input type="checkbox" name="u{{ $i }}r9"{{ $rechtehand }}></td>
</tr>
