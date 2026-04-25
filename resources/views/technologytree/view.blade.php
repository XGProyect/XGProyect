@extends('master.game')

@section('content')
<table width="569">
    <tr>
        <td class="c" colspan="2">{{ __('game/constructions.construction') }}</td>
    </tr>
    @foreach ($constructions as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['id'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['id'] . '.gif') }}" alt="{{ $item['name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent; font-weight: normal;" align="right">{{ $item['detail'] }}</td>
                </tr>
            </table>
        </th>
        <th role="cell" class="l" width="60%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{!! $item['requirements'] !!}</td>
                </tr>
            </table>
        </th>
    </tr>
    @endforeach
    <tr>
        <td class="c" colspan="2">{{ __('game/technologies.research') }}</td>
    </tr>
    @foreach ($research as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['id'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['id'] . '.gif') }}" alt="{{ $item['name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['detail'] }}</td>
                </tr>
            </table>
        </th>
        <th role="cell" class="l" width="60%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{!! $item['requirements'] !!}</td>
                </tr>
            </table>
        </th>
    </tr>
    @endforeach
    <tr>
        <td class="c" colspan="2">{{ __('game/ships.ships') }}</td>
    </tr>
    @foreach ($ships as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['id'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['id'] . '.gif') }}" alt="{{ $item['name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['detail'] }}</td>
                </tr>
            </table>
        </th>
        <th role="cell" class="l" width="60%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{!! $item['requirements'] !!}</td>
                </tr>
            </table>
        </th>
    </tr>
    @endforeach
    <tr>
        <td class="c" colspan="2">{{ __('game/defenses.defenses') }}</td>
    </tr>
    @foreach ($defenses as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['id'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['id'] . '.gif') }}" alt="{{ $item['name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['detail'] }}</td>
                </tr>
            </table>
        </th>
        <th role="cell" class="l" width="60%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{!! $item['requirements'] !!}</td>
                </tr>
            </table>
        </th>
    </tr>
    @endforeach
    <tr>
        <td class="c" colspan="2">{{ __('game/defenses.missiles') }}</td>
    </tr>
    @foreach ($missiles as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['id'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['id'] . '.gif') }}" alt="{{ $item['name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['detail'] }}</td>
                </tr>
            </table>
        </th>
        <th role="cell" class="l" width="60%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{!! $item['requirements'] !!}</td>
                </tr>
            </table>
        </th>
    </tr>
    @endforeach
</table>
@endsection