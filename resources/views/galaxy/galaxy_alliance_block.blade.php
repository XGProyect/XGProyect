<a
	style="cursor: pointer;"
	onmouseover="return overlib('<table role=presentation width=240><tr><td class=c>{{ __('game/galaxy.gl_alliance') }} {{ $alliance_name }}{{ __('game/galaxy.gl_with') }}{{ $ally_members }}{{ __('game/galaxy.gl_member') }}{{ $add }}</td></tr><th role=cell><table role=presentation><tr><td><a href=game.php?page=alliance&mode=ainfo&allyid={{ $ally_id }}>{{ __('game/galaxy.gl_alliance_page') }}</a></td></tr><tr><td><a href=game.php?page=statistics&start=101&who=2>{{ __('game/galaxy.gl_see_on_stats') }}</a></td>{{ $web }}</tr></table></th></table>', STICKY, MOUSEOFF, DELAY, 750, CENTER, OFFSETX, -40, OFFSETY, -40 );"
	onmouseout="return nd();">
	{!! $tag !!}
</a>