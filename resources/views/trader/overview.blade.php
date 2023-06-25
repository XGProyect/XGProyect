@extends('master.game')

@section('content')
<x-notice width="519px" :color="$color" :message="$message" />

<table width="665px">
    <tr>
        <th width="50%">
            <a href="game.php?page=traderResources" title="{{ __('game/trader.tr_resource_market_title') }}">
                {{ __('game/trader.tr_resource_market') }}
            <a>
        </th>
        <!--<th width="50%">
            <a href="game.php?page=traderAuctioneer" title="{tr_auctioneer_title}">{tr_auctioneer}<a>
        </th>-->
    </tr>
    <!--<tr>
        <th width="50%">
            <a href="game.php?page=traderScrap" title="{tr_scrap_merchant_title}">{tr_scrap_merchant}<a>
        </th>
        <th width="50%">
            <a href="game.php?page=traderImportExport" title="{tr_import_export_title}">{tr_import_export}<a>
        </th>
    </tr>-->
</table>

{!! $currentMode !!}
@endsection