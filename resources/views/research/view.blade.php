@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <font color="#ff0000">{!! $noresearch !!}</font>
    <table align="top" width="530">
        @foreach ($technologies as $item)
        <tr>
            <th class="l">
                <a href="game.php?page=technologydetails&technology={{ $item['tech_id'] }}">
                    <img border="0" src="{{ asset('upload/skins/xgproyect/elements/' . $item['tech_id'] . '.gif') }}" align="top" width="120" height="120" alt="{{ $item['tech_name'] }}"/>
                </a>
            </th>
            <td class="l">
                <a href="game.php?page=technologydetails&technology={{ $item['tech_id'] }}">
                    {{ $item['tech_name'] }}
                </a>
                {!! $item['tech_level'] !!}
                <br>
                {{ $item['tech_descr'] }}
                <br>
                {!! $item['tech_price'] !!}
                {!! $item['search_time'] !!}
            </td>
            <th role="cell" class="l">
                {!! $item['tech_link'] !!}
            </th>
        </tr>
        @endforeach
    </table>
</div>
@endsection