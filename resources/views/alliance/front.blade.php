@extends('master.game')

@section('content')
<table width="519px">
    <tr>
        <td class="c" colspan="2">{{ __('game/alliance.al_your_ally') }}</td>
    </tr>
    {!! $image !!}
    @foreach ($details as $item)
    <tr>
        <th scope="row">{!! $item['detail_title'] !!}</th>
        <th role="cell">{!! $item['detail_content'] !!}</th>
    </tr>
    @endforeach
    {!! $description !!}
    <tr>
        <td class="c" colspan="2">{{ __('game/alliance.al_inside_section') }}</th>
    </tr>
    <tr>
        <th role="cell" colspan="2" height="100px">{!! $text !!}</th>
    </tr>
</table>
@if ($leave)
<table width="519" role="presentation">
    <tr>
        <td role="heading" aria-level="2" class="c">{{ __('game/alliance.al_leave_alliance') }}</td>
    </tr>
    <tr>
        <th>
            <input type="button" onclick="javascript:location.href='game.php?page=alliance&mode=exit';" value="{{ __('game/alliance.al_continue') }}"/>
        </th>
    </tr>
</table>
@endif
@endsection