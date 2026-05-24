@php
    $collectAction = sprintf(
        'doit(8, %d, %d, %d, %d, %d); return nd();',
        (int) $galaxy,
        (int) $system,
        (int) $planet,
        (int) $planettype,
        (int) $recsended,
    );

    $tooltip = '<table role="presentation" width="240">'
        . '<tr><td class="c" colspan="2">' . e(__('game/galaxy.gl_debris_field')) . ' [' . (int) $galaxy . ':' . (int) $system . ':' . (int) $planet . ']</td></tr>'
        . '<tr><th width="80"><img src="' . e($image) . '" height="75" width="75" alt=""></th>'
        . '<th><table>'
        . '<tr><td class="c" colspan="2">' . e(__('game/galaxy.gl_resources')) . ':</td></tr>'
        . '<tr><th scope="row">' . e(__('game/global.metal')) . ': </th><th role="cell">' . e($planet_debris_metal) . '</th></tr>'
        . '<tr><th scope="row">' . e(__('game/global.crystal')) . ': </th><th role="cell">' . e($planet_debris_crystal) . '</th></tr>'
        . '<tr><td class="c" colspan="2">' . e(__('game/galaxy.gl_actions')) . ':</td></tr>'
        . '<tr><th role="cell" colspan="2" align="left"><a href="#" onclick="' . e($collectAction) . '">' . e(__('game/galaxy.gl_collect')) . '</a></th></tr>'
        . '</table></th></tr></table>';
@endphp

<a
    style="cursor: pointer;"
    onmouseover='return overlib(@json($tooltip), STICKY, MOUSEOFF, DELAY, 750, CENTER, OFFSETX, -40, OFFSETY, -40);'
    onmouseout="return nd();">
    <img src="{{ $image }}" height="22px" width="22px" alt="{{ __('game/global.metal') }}: {{ $planet_debris_metal }}, {{ __('game/global.crystal') }}: {{ $planet_debris_crystal }}"/>
</a>
