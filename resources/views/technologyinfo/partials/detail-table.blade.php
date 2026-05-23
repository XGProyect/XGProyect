<table border="1" style="margin: 0 auto;">
    <tbody>
        <tr>
            @foreach ($detailTable['headers'] as $header)
            <td role="columnheader" class="c">{{ $header }}</td>
            @endforeach
        </tr>
        @foreach ($detailTable['rows'] as $row)
        <tr>
            <th scope="row">{!! $row[0] !!}</th>
            @foreach (array_slice($row, 1) as $cell)
            <th role="cell">{!! $cell !!}</th>
            @endforeach
        </tr>
        @endforeach
        @foreach ($detailTable['footerRows'] as $footer)
        <tr>
            <th role="cell" colspan="{{ count($detailTable['headers']) }}">{{ $footer }}</th>
        </tr>
        @endforeach
    </tbody>
</table>