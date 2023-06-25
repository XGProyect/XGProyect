@extends('master.game')

@section('content')
<form name="stats" method="post" role="form">
    <table width="519">
        <tr>
            <td colspan="6" class="c">
                {{ __('game/highscore.st_statistics') }}
            </td>
        </tr>
        <tr>
            <th colspan="6" class="c">
                {{ __('game/highscore.st_show') }}
                <select name="who" onChange="javascript:document.stats.submit()">
                    {!! $who !!}
                </select>
                {{ __('game/highscore.st_per') }}
                <select name="type" onChange="javascript:document.stats.submit()">
                    {!! $type !!}
                </select>
                {{ __('game/highscore.st_in_the_positions') }}
                <select name="range" onChange="javascript:document.stats.submit()">
                    {!! $range !!}
                </select>
            </th>
        <tr>
    </table>
</form>
<table width="519">
    {!! $stat_header !!}
    {!! $stat_values !!}
</table>
@endsection