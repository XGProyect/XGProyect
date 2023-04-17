@extends('master.game')

@section('content')
<br />
<div id="content" role="main">
    <form action="" method="POST" role="form">
        <table width="519px">
            <tr>
                <td class="c">{{ __('game/search.sh_search_universe') }}</td>
            </tr>
            <tr>
                <th>
                    {{ __('game/search.sh_put_in_leyend') }}
                    <br><br>
                    <select name="searchType">
                        <option value="playerName"{{ $playerName }}>{{ __('game/search.sh_option_player_name') }}</option>
                        <option value="allianceTag"{{ $allianceTag }}>{{ __('game/search.sh_option_alliance_tag') }}</option>
                        <option value="planetNames"{{ $planetNames }}>{{ __('game/search.sh_option_planet_names') }}</option>
                    </select>
                    &nbsp;&nbsp;
                    <input type="text" name="searchText" value="{{ $searchText }}">
                    &nbsp;&nbsp;

                    <input type="submit" value="{{ __('game/search.sh_search_button') }}">
                </th>
            </tr>
        </table>
    </form>
    {{ $errorBlock }}
    {!! $searchResults !!}
</div>
@endsection