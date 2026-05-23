<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(235px, 1fr)); gap: 4px 18px; margin-top: 10px; text-align: left;">
    @foreach ($items as $item)
    <div style="font-weight: normal;">
        &bull; {{ $item['label'] }}: <span style="color: {{ $item['color'] }};">{{ $item['chance'] }}% ({{ $item['shots'] }})</span>
    </div>
    @endforeach
</div>