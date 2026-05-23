@extends('master.game')

@section('content')
@if ($errors->has('jmpto'))
<div style="width: 519px; margin: 0 auto;">
    <x-notice width="519px" color="red" :message="$errors->first('jmpto')" />
</div>
@endif

<div style="width: 519px; margin: 0 auto;">
    <x-notice width="519px" :color="session('technologyinfo_notice_color', '')" :message="session('technologyinfo_notice_message', '')" />
</div>

@include('technologyinfo.panel', ['info' => $techInfo])

<table width="569" style="margin: 8px auto 0;">
    <tr>
        <td class="c" colspan="2">{{ __('game/technologydetails.techtree_title') }}</td>
    </tr>
    <tr>
        <th scope="row" class="l" width="40%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent;" align="left">
                        <a href="game.php?page=technologydetails&technology={{ $id }}" style="text-decoration: none;">
                            <img src="{{ asset('assets/upload/skins/xgproyect/elements/' . $id . '.gif') }}" alt="{{ $name }}" style="height: 28px; width: 28px;">
                            <span style="vertical-align: top; font-weight: normal;">{{ $name }}</span>
                        </a>
                    </td>
                </tr>
            </table>
        </th>
        <th role="cell" class="l" width="60%">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{!! $requirements !!}</td>
                </tr>
            </table>
        </th>
    </tr>
</table>

<table width="569" style="margin: 8px auto 0;">
    <tr>
        <td class="c" colspan="2">{{ $applicationsTitle }}</td>
    </tr>
    @forelse ($applications as $item)
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
    @empty
    <tr>
        <th role="cell" colspan="2" class="l">
            <table width="100%">
                <tr>
                    <td style="background-color: transparent; font-weight: normal;" align="left">{{ __('game/technologydetails.no_applications') }}</td>
                </tr>
            </table>
        </th>
    </tr>
    @endforelse
</table>
@endsection