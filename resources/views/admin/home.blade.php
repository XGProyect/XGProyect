@extends('master.admin')

@section('content')
<div class="container-fluid">

    <x-admin.status-alert :message="$errorMessage" :style="$secondStyle" :type="$errorType"/>

    <!-- Page Heading -->
    <x-admin.page-header
        :title="__('admin/home.hm_title')"
        :subtitle="__('admin/home.hm_sub_title')"
    />

    <x-admin.card-collapsible id="collapseStatistics" :title="__('admin/home.hm_server_statistics')" icon="fas fa-server">
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <td>{{ __('admin/home.hm_number_users') }}:</td>
                        <td>{{ $numberUsers }}</td>
                        <td>{{ __('admin/home.hm_number_alliances') }}:</td>
                        <td>{{ $numberAlliances }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin/home.hm_number_planets') }}:</td>
                        <td>{{ $numberPlanets }}</td>
                        <td>{{ __('admin/home.hm_number_moons') }}:</td>
                        <td>{{ $numberMoons }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin/home.hm_number_fleets') }}:</td>
                        <td>{{ $numberFleets }}</td>
                        <td>{{ __('admin/home.hm_number_reports') }}:</td>
                        <td>{{ $numberReports }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin/home.hm_average_user_points') }}:</td>
                        <td>≃{{ $averageUserPoints }}</td>
                        <td>{{ __('admin/home.hm_average_alliance_points') }}:</td>
                        <td>≃{{ $averageAlliancePoints }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin/home.hm_database_size') }}:</td>
                        <td>{{ $databaseSize }}</td>
                        <td>{{ __('admin/home.hm_database_server') }}</td>
                        <td>{{ $databaseServer }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('admin/home.hm_php_version') }}:</td>
                        <td>{{ $phpVersion }}</td>
                        <td>{{ __('admin/home.hm_server_version') }}</td>
                        <td>{{ $serverVersion }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </x-admin.card-collapsible>

    <x-admin.card-collapsible id="collapseCredits" :title="__('admin/home.hm_credits')" icon="fas fa-heart">
        <div class="text-center">
            <p>
                <strong>{{ __('admin/home.hm_proyect_leader') }}</strong><br>
                <a href="https://github.com/LucasKovacs" target="_blank">lucky</a><br>
                <a href="https://github.com/BeReal86" target="_blank">BeReal</a>
                <br><br>
                <strong>{{ __('admin/home.hm_principal_contributors') }}</strong><br>
                <a href="https://github.com/FGServers" target="_blank">JonaMix</a>
                <br><br>
                <strong>{{ __('admin/home.hm_extensions') }}</strong><br>
                <a href="https://laravel.com/" target="_blank">Laravel | Core</a><br>
                <a href="https://github.com/jstar88/opbe" target="_blank">jstar - OPBE</a><br>
                <a href="https://startbootstrap.com/themes/sb-admin-2/" target="_blank">Start Bootstrap | SB Admin 2</a>
                <br><br>
                <strong>{{ __('admin/home.hm_thanks_to') }}</strong><br>
                adri93, Alberto14, angelus_ira, Anghelito, Arali, Borboco, Calzon, cyberghoser1, cyberrichy,
                duhow, edering, Gmir17, Green, jtsamper, Kloud, LordPretender, Loucouss, medel, MSW,
                Neko, Neurus, Nickolay, Pada, pele87, PowerMaster, privatethedawn, quaua, Razican, Tarta, Think,
                thyphoon, tomtom, Tonique, Trojan, Saint, shoghicp, slaver7, war4head, zebulonbof, zorro2666
            </p>
        </div>
    </x-admin.card-collapsible>
</div>
@endsection