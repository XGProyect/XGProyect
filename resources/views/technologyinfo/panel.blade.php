@php
    $summary = $info['summary'];
    $detailTable = $info['detailTable'];
@endphp

@if ($summary['type'] === 'combat')
<table width="519" style="margin: 0 auto;">
    <tbody>
        <tr>
            <td class="c" colspan="2">{{ $summary['title'] }}</td>
        </tr>
        <tr>
            <th scope="row">{{ $summary['nameLabel'] }}</th>
            <th role="cell">{{ $summary['name'] }}</th>
        </tr>
        <tr>
            <th role="cell" colspan="2">
                <table role="presentation">
                    <tbody>
                        <tr>
                            <td>
                                <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $summary['imageId'] . '.gif') }}" align="top" border="0" height="120" width="120" alt="{{ $summary['name'] }}">
                            </td>
                            <td>
                                {!! $summary['description'] !!}
                                @if ($summary['extraHtml'] !== '')
                                <br><br>{!! $summary['extraHtml'] !!}
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </th>
        </tr>
        @foreach ($summary['stats'] as $stat)
        <tr>
            <th scope="row">{{ $stat['label'] }}</th>
            <th role="cell">{!! $stat['value'] !!}</th>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<table role="presentation" width="519" style="margin: 0 auto;">
    <tbody>
        <tr>
            <td class="c">{{ $summary['title'] }}</td>
        </tr>
        <tr>
            <th>
                <table role="presentation">
                    <tbody>
                        <tr>
                            <td>
                                <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $summary['imageId'] . '.gif') }}" align="top" border="0" height="120" width="120" alt="{{ $summary['title'] }}">
                            </td>
                            <td>{!! $summary['description'] !!}</td>
                        </tr>
                    </tbody>
                </table>
            </th>
        </tr>
        @if ($detailTable !== null)
        <tr>
            <th>
                @include('technologyinfo.partials.detail-table', ['detailTable' => $detailTable])
            </th>
        </tr>
        @endif
    </tbody>
</table>
@endif

@if ($info['jumpGate'] !== null)
@include('technologyinfo.partials.jump-gate', ['jumpGate' => $info['jumpGate']])
@endif

@if ($info['tearDown'] !== null)
@include('technologyinfo.partials.tear-down', ['tearDown' => $info['tearDown']])
@endif