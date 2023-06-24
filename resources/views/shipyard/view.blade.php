@extends('master.game')

@section('content')
    <br>
    <div id="content" role="main">
        {{ $message }}
        <form action="" method="post" role="form">
            <table align="top" width="530">
                @foreach ($list_of_items as $item)
                <tr>
                    <th scope="row" class="l">
                        <a href="game.php?page=technologydetails&technology={{ $item['element'] }}">
                            <img border="0" src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['element'] . '.gif') }}" align="top" width="120px" height="120px" alt="{{ $item['element_name'] }}"/>
                        </a>
                    </th>
                    <td class="l">
                        <a href="game.php?page=technologydetails&technology={{ $item['element'] }}">{{ $item['element_name'] }}</a> {{ $item['element_nbre'] }}<br>
                        {!! $item['element_description'] !!}<br>
                        {!! $item['element_price'] !!}
                        {!! $item['building_time'] !!}
                    </td>
                    <th role="cell" class="k">
                        {!! $item['add_element'] !!}
                    </th>
                </tr>
                @endforeach
                {!! $build_button !!}
            </table>
        </form>
        {!! $building_list !!}
    </div>
@endsection