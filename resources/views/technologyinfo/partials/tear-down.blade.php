<table role="presentation" width="519" style="margin: 8px auto 0;">
    <tbody>
        <tr>
            <td class="c" align="center">
                <a href="{{ $tearDown['url'] }}">{{ $tearDown['label'] }}</a>
            </td>
        </tr>
        <tr>
            <th>{{ $tearDown['costsLabel'] }}</th>
        </tr>
        <tr>
            <th>{!! $tearDown['ionBonusHtml'] !!}</th>
        </tr>
        <tr>
            <th>
                @foreach ($tearDown['resources'] as $resource)
                {{ $resource['label'] }}: <b>{{ $resource['value'] }}</b><br>
                @endforeach
                {{ $tearDown['durationLabel'] }}: {{ $tearDown['duration'] }}<br>
            </th>
        </tr>
    </tbody>
</table>