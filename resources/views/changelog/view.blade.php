@extends('master.game')

@section('content')
<table width="668px">
    <tr>
        <td role="columnheader" class="c">{{ __('game/changelog.ch_version') }}</td>
        <td role="columnheader" class="c">{{ __('game/changelog.ch_description') }}</td>
    </tr>
    @foreach ($changes as $item)
    <tr>
        <th scope="row" width="42px">{{ $item['versionNumber'] }}</th>
        <td style="text-align: left" class="b">{!! $item['description'] !!}</td>
    </tr>
    @endforeach
</table>
@endsection