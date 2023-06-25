@extends('master.game')

@section('content')
<form action="" method="POST" role="form">
    <table width="519">
        <tr>
            <td class="c" colspan="2">{{ __('game/alliance.al_find_alliances') }}</td>
        </tr>
        <tr>
            <th scope="row">{{ __('game/alliance.al_find_text') }}</th>
            <th role="cell">
                <input type="text" name="searchtext" value="{{ $searchtext }}"/>
                <input type="submit" value="{{ __('game/alliance.al_find_submit') }}"/>
            </th>
        </tr>
    </table>
</form>
@if ($searchResults)
<table width="519">
    <tr>
        <td class="c" colspan="3">{{ __('game/alliance.al_the_nexts_allys_was_founded') }}</td>
    </tr>
    <tr>
        <td role="columnheader" class="c">{{ __('game/alliance.al_ally_info_tag') }}</td>
        <td role="columnheader" class="c">{{ __('game/alliance.al_ally_info_name') }}</td>
        <td role="columnheader" class="c">{{ __('game/alliance.al_ally_info_members') }}</td>
    </tr>
    @foreach ($searchResults as $item)
    <tr>
        <th scope="row">{!! $item['ally_tag'] !!}</th>
        <th role="cell">{!! $item['alliance_name'] !!}</th>
        <th role="cell">{!! $item['ally_members'] !!}</th>
    </tr>
    @endforeach
</table>
@endif
@endsection