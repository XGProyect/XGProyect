@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table border="0" cellpadding="0" cellspacing="1" width="519">
        <tr height="20">
            <td class="c" colspan="2">
                {{ __('game/fleet.fl_shortcuts') }} (<a href="game.php?page=shortcuts&mode=add">{{ __('game/fleet.fl_shortcut_add') }}</a>)
            </td>
        </tr>
        @forelse ($shortcuts as $item)
            {!! $item['row_start'] !!}
            <th role="cell">
                <a href="game.php?page=shortcuts&mode=edit&a={{ $item['shortcut_id'] }}">
                    {{ $item['shortcut_name'] }} {{ $item['shortcut_coords'] }} {{ $item['shortcut_type'] }}
                </a>
            </th>
            {!! $item['row_end'] !!}
        @empty
        <tr>
            <th colspan="2">
                {{ __('game/fleet.fl_no_shortcuts') }}
            </th>
        </tr>
        @endforelse
        <tr>
            <td class="c" colspan="2">
                <a href="game.php?page=fleet1">{{ __('game/fleet.fl_back') }}</a>
            </td>
        </tr>
    </table>
</div>
@endsection