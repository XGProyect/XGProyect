@extends('master.game')

@section('content')
<table width="569">
    <tr>
        <td class="c" colspan="2">{{ __('game/constructions.construction') }}</td>
    </tr>
    @foreach ($list_of_constructions as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['tt_info'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['tt_info'] . '.gif') }}" alt="{{ $item['tt_name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['tt_name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent; font-weight: normal;" align="right">{{ $item['tt_detail'] }}</td>
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
    @foreach ($list_of_research as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['tt_info'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['tt_info'] . '.gif') }}" alt="{{ $item['tt_name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['tt_name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['tt_detail'] }}</td>
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
    @foreach ($list_of_ships as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['tt_info'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['tt_info'] . '.gif') }}" alt="{{ $item['tt_name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['tt_name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['tt_detail'] }}</td>
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
    @foreach ($list_of_defenses as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['tt_info'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['tt_info'] . '.gif') }}" alt="{{ $item['tt_name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['tt_name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['tt_detail'] }}</td>
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
    @foreach ($list_of_missiles as $item)
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $item['tt_info'] }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $item['tt_info'] . '.gif') }}" alt="{{ $item['tt_name'] }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $item['tt_name'] }}</span>
                        </a>
                    </td>
                    <td style="background-color: transparent;font-weight: normal;" align="right">{{ $item['tt_detail'] }}</td>
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