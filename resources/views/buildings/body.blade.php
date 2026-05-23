@extends('master.game')

@section('content')
{!! $BuildListScript !!}
<table width="530">
    @include('game.queue_rows', ['rows' => $queueRows])
    @foreach ($list_of_buildings as $item)
    <tr>
        <td class="l" width="120" height="120">
            <a href="game.php?page=technologydetails&technology={{ $item['i'] }}">
                <img alt="{{ $item['n'] }}" border="0" src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['i'] . '.gif') }}" align="top" width="120" height="120">
            </a>
        </td>
        <td class="l">
            <a href="game.php?page=technologydetails&technology={{ $item['i'] }}">{{ $item['n'] }}</a>{{ $item['nivel'] }}<br>
            {{ $item['descriptions'] }}<br>
            {!! $item['price'] !!}
            {!! $item['time'] !!}
        </td>
        <td class="k">{!! $item['click'] !!}</td>
    </tr>
    @endforeach
</table>
@endsection