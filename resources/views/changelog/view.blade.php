@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table width="668px">
        <tr>
            <td role="columnheader" class="c">{{ __('game/changelog.ch_version') }}</td>
            <td role="columnheader" class="c">{{ __('game/changelog.ch_description') }}</td>
        </tr>
        @foreach ($changes as $item)
        <tr>
            <th scope="row" width="42px">{{ $item['version_number'] }}</th>
            <td style="text-align: left" class="b">{!! $item['description'] !!}</td>
        </tr>
        @endforeach
    </table>
</div>
@endsection