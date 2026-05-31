@extends('master.game')

@section('content')
@php
    use App\Http\Requests\Game\HighscoreRequest;

    $whoOptions = [
        HighscoreRequest::WHO_PLAYER => __('game/highscore.st_player'),
        HighscoreRequest::WHO_ALLIANCE => __('game/highscore.st_alliance'),
    ];

    $typeOptions = [
        HighscoreRequest::TYPE_TOTAL => __('game/highscore.st_total'),
        HighscoreRequest::TYPE_ECONOMY => __('game/highscore.st_economy'),
        HighscoreRequest::TYPE_RESEARCH => __('game/highscore.st_research'),
        HighscoreRequest::TYPE_MILITARY => __('game/highscore.st_military'),
    ];
@endphp

<form name="stats" method="get" action="game.php" role="form">
    <input type="hidden" name="page" value="highscore">
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
                    @foreach ($whoOptions as $value => $label)
                        <option value="{{ $value }}" @selected($who === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                {{ __('game/highscore.st_per') }}
                <select name="type" onChange="javascript:document.stats.submit()">
                    @foreach ($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                {{ __('game/highscore.st_in_the_positions') }}
                <select name="range" onChange="javascript:document.stats.submit()">
                    @foreach ($pagination['pages'] as $page)
                        <option value="{{ $page['value'] }}" @selected($page['active'])>{{ $page['label'] }}</option>
                    @endforeach
                </select>
            </th>
        </tr>
    </table>
</form>

<table width="519">
    @if ($who === HighscoreRequest::WHO_ALLIANCE)
        @include('highscore.partials.alliance_table', ['rows' => $rows])
    @else
        @include('highscore.partials.player_table', ['rows' => $rows])
    @endif
</table>
@endsection
