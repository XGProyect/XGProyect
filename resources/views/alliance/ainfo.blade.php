@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table width="519px">
        <tr>
            <td class="c" colspan="2">{{ __('game/alliance.al_ally_information') }}</td>
        </tr>
        {!! $image !!}
        <tr>
            <th scope="row">{{ __('game/alliance.al_ally_info_tag') }}</th>
            <th role="cell">{{ $tag }}</th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/alliance.al_ally_info_name') }}</th>
            <th role="cell">{{ $name }}</th>
        </tr>
        <tr>
            <th scope="row">{{ __('game/alliance.al_ally_info_members') }}</th>
            <th role="cell">{{ $members }}</th>
        </tr>
        {!! $description !!}
        <tr>
            <th scope="row">{{ __('game/alliance.al_web_text') }}</th>
            <th role="cell">{!! $web !!}</th>
        </tr>
        {!! $requests !!}
    </table>
</div>
@endsection