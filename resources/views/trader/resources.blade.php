<table width="665px">
    <tr>
        <td class="c">{{ __('game/trader.tr_resource_market') }}</td>
    </tr>
    <tr>
        <td>
            <form name="refill-resources" method="POST" action="" role="form">
                <table width="100%">
                    <tr>
                        <td class="c" colspan="2">{{ __('game/trader.tr_merchant1_tab_title') }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <h2>{{ __('game/trader.tr_merchant1_title') }}</h2>
                            <p>{{ __('game/trader.tr_merchant1_explanation') }}</p>
                        </th>
                    </tr>
                    <tr>
                        <td class="c" colspan="2">
                            <h3>{{ __('game/trader.tr_merchant1_info') }}</h3>
                        </td>
                    </tr>
                    @foreach ($resourcesList as $resource)
                    <tr>
                        <th>
                            <img border="0" src="{{ asset('assets/upload/skins/xgproyect/resources/' . $resource['resource'] . '.gif') }}" width="42" height="22" alt=""/>
                            <br>
                            {{ $resource['resourceName'] }}
                        </th>
                        <td>
                            <table width="100%">
                                <tr>
                                    <th colspan="3">
                                        {{ __('game/trader.tr_storage_capacity') }}:
                                        {{ $resource['currentResource'] }} / {{ $resource['maxResource'] }}
                                    </th>
                                </tr>
                                <tr>
                                    @foreach ($resource['refillOptions'] as $option)
                                    <th>
                                        {{ $option['label'] }}:<br>
                                        <span style="font-size: 3em">{{ $option['percentage'] }}%</span><br>
                                        {{ __('game/trader.tr_requires') }}:<br>
                                        {!! $option['price'] !!}<br>
                                        {!! $option['button'] !!}
                                    </th>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </form>
            <form name="trade-resources" method="POST" action="" role="form">
                <table width="100%">
                    <tr>
                        <td class="c" colspan="2">{{ __('game/trader.tr_merchant2_tab_title') }}</td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <h2>{{ __('game/trader.tr_merchant2_title') }}</h2>
                        </th>
                    </tr>
                    <tr>
                        <th style="text-align:left">
                            {{ __('game/trader.tr_step1') }}
                        </th>
                        <th style="text-align:left">
                            {{ __('game/trader.tr_step2') }}
                        </th>
                    </tr>
                    <tr>
                        <th width="50%">
                            <table width="100%">
                                <tr>
                                    <th>
                                        <img border="0" src="{{ asset('assets/upload/skins/xgproyect/resources/metal.gif') }}" width="42" height="22" alt=""/>
                                    </th>
                                    <th>
                                        <img border="0" src="{{ asset('assets/upload/skins/xgproyect/resources/crystal.gif') }}" width="42" height="22" alt=""/>
                                    </th>
                                    <th>
                                        <img border="0" src="{{ asset('assets/upload/skins/xgproyect/resources/deuterium.gif') }}" width="42" height="22" alt=""/>
                                    </th>
                                </tr>
                                <tr>
                                    <th>
                                        <a title="Sell your Metal and get Crystal or Deuterium. Costs: 3.500 Dark Matter">Metal</a>
                                        <input type="radio" name="sell" value="metal">
                                    </th>
                                    <th>
                                        <a title="Sell your Crystal and get Metal or Deuterium. Costs: 3.500 Dark Matter">Crystal</a>
                                        <input type="radio" name="sell" value="crystal">
                                    </th>
                                    <th>
                                        <a title="Sell your Deuterium and get Metal or Crystal. Costs: 3.500 Dark Matter">Deuterium</a>
                                        <input type="radio" name="sell" value="deuterium">
                                    </th>
                                </tr>
                            </table>
                        </th>
                        <th width="50%">
                            {{ __('game/trader.tr_price') }}<br>
                            <input type="button" value="{{ __('game/trader.tr_call_button') }}">
                        </th>
                    </tr>
                </table>
            </form>
        </td>
    </tr>
</table>