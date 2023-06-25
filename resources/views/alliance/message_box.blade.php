@extends('master.game')

@section('content')
<form action="{{ $goto }}" method="post" role="form">
    <table width="519">
        <tr>
            <td class="c" colspan="2">{{ $title }}</td>
        </tr>
        @if ($oneRow)
        <tr>
            <th role="cell">
                {!! $message !!}
                <input type="submit" value="{{ $button }}">
            </th>
        </tr>
        @else
        <tr>
            <th role="cell" colspan="2">
                {!! $message !!}
            </th>
        </tr>
        <tr>
            <th role="cell" colspan="2" align="center">
                <input type="submit" value="{{ $button }}">
            </th>
        </tr>
        @endif
    </table>
</form>
@endsection