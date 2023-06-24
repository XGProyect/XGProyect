@extends('master.game')

@section('content')
<br>
<div id="content" role="main">
    <table role="presentation" width="600">
        <tr>
            <td role="heading" aria-level="2" colspan="3" class="c">{{ __('game/global.darkmatter') }}</td>
        </tr>
        <tr>
            <td class="l">
                <img src="{{ asset('assets/upload/skins/xgproyect/premium/DMaterie.jpg') }}" width="120" height="120" alt=""/>
            </td>
            <td class="l">
                <strong>{{ __('game/global.darkmatter') }}</strong><br>
                {{ __('game/officier.of_darkmatter_description') }}
                <div style="margin:4px 4px;">
                    <table role="presentation">
                        <tr>
                            <td>
                                <img src="{{ asset('assets/upload/skins/xgproyect/premium/dm_klein_1.jpg') }}" width="32" height="32" style="vertical-align:middle;" alt=""/></td>
                            <td style='background-color:transparent;'>
                                <strong style="color:skyblue; vertical-align:middle;">{{ __('game/officier.of_darkmatter_description_short') }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="l" style="width:90px;text-align:center; vertical-align:middle;">
                <a id='darkmatter2' href='{{ $premium_pay_url }}' style='cursor:pointer; text-align:center;width:100px;height:60px;'><br>
                    <div id='darkmatter2'><strong>{{ __('game/officier.of_get_darkmatter') }}</strong></div>
                </a>
            </td>
        </tr>
        <tr>
            <td role="heading" aria-level="2" colspan="3" class="c">{{ __('game/officier.of_title') }}</td>
        </tr>
        @foreach ($officier_list as $item)
        <tr>
            <td class="l" rowspan="2">
                <img src="{{ asset('assets/upload/skins/xgproyect/premium/' . $item['img_big'] .'.jpg') }}" width="120" height="120" alt=""/>
            </td>
            <td class="l" rowspan="2">
                <strong role="heading" aria-level="3">{{ $item['name'] }}</strong> (<strong>{!! $item['status'] !!}</strong>)<br>
                {{ $item['description'] }}
                <div style="margin:4px 4px;">
                    <table role="presentation">
                        <tr>
                            <td>
                                <img src="{{ asset('assets/upload/skins/xgproyect/premium/' . $item['img_small'] .'.gif') }}" width="32" height="32" style="vertical-align:middle;" alt="{{ $item['name'] }}"/>
                            </td>
                            <td style='background-color:transparent;'>
                                <strong style="color:skyblue; vertical-align:middle;">{{ $item['benefits'] }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="l" style="width:90px;text-align:center; vertical-align:middle;">
                <a href='{{ $item['link_week'] }}' >
                    <strong>
                            {{ __('game/officier.of_week') }}<br>
                            <span style="color:lime">{{ $item['week_price'] }}</span>
                            <br>{{ __('game/global.darkmatter') }}
                    </strong>
                </a>
            </td>
        </tr>
        <tr>
            <td class="l" style="width:90px;text-align:center; vertical-align:middle;">
                <a href='{{ $item['link_month'] }}'>
                    <strong>
                        {{ __('game/officier.of_months') }}<br>
                        <span style="color:lime">{{ $item['month_price'] }}</span>
                        <br>{{ __('game/global.darkmatter') }}
                    </strong>
                </a>
            </td>
        </tr>
        <tr>
            <td colspan="3" class="c" style='height:4px;'></td>
        </tr>
        @endforeach
    </table>
</div>
@endsection