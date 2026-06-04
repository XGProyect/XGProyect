@extends('master.admin')

@section('content')
<x-admin.status-alert :message="$errorMessage" :style="$secondStyle" :type="$errorType"/>

<x-admin.page-header
    :title="__('admin/home.hm_title')"
    :subtitle="__('admin/home.hm_sub_title')"
/>

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @php
        $stats = [
            ['label' => __('admin/home.hm_number_users'), 'value' => $numberUsers, 'icon' => 'users'],
            ['label' => __('admin/home.hm_number_alliances'), 'value' => $numberAlliances, 'icon' => 'shield'],
            ['label' => __('admin/home.hm_number_planets'), 'value' => $numberPlanets, 'icon' => 'globe'],
            ['label' => __('admin/home.hm_number_moons'), 'value' => $numberMoons, 'icon' => 'moon'],
            ['label' => __('admin/home.hm_number_fleets'), 'value' => $numberFleets, 'icon' => 'rocket'],
            ['label' => __('admin/home.hm_number_reports'), 'value' => $numberReports, 'icon' => 'scroll'],
        ];
    @endphp

    @foreach ($stats as $stat)
        <div class="adm-stat">
            <div class="adm-stat-header">
                <span class="adm-stat-label">{{ $stat['label'] }}</span>
                <i data-lucide="{{ $stat['icon'] }}" class="adm-stat-icon"></i>
            </div>
            <span class="adm-stat-value">{{ $stat['value'] }}</span>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <x-admin.card-collapsible id="collapseStatistics" :title="__('admin/home.hm_server_statistics')" icon="server">
        <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-[13px]">
            <div>
                <dt class="text-fg-muted">{{ __('admin/home.hm_average_user_points') }}</dt>
                <dd class="mt-0.5 font-medium text-fg">≃{{ $averageUserPoints }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">{{ __('admin/home.hm_average_alliance_points') }}</dt>
                <dd class="mt-0.5 font-medium text-fg">≃{{ $averageAlliancePoints }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">{{ __('admin/home.hm_database_size') }}</dt>
                <dd class="mt-0.5 font-medium text-fg">{{ $databaseSize }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">{{ __('admin/home.hm_database_server') }}</dt>
                <dd class="mt-0.5 font-medium text-fg">{{ $databaseServer }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">{{ __('admin/home.hm_php_version') }}</dt>
                <dd class="mt-0.5 font-medium text-fg">{{ $phpVersion }}</dd>
            </div>
            <div>
                <dt class="text-fg-muted">{{ __('admin/home.hm_server_version') }}</dt>
                <dd class="mt-0.5 font-medium text-fg">{{ $serverVersion }}</dd>
            </div>
        </dl>
    </x-admin.card-collapsible>

    <x-admin.card-collapsible id="collapseCredits" :title="__('admin/home.hm_credits')" icon="heart">
        <div class="text-center text-[13px] space-y-5">
            <div>
                <p class="font-semibold text-fg">{{ __('admin/home.hm_proyect_leader') }}</p>
                <p class="mt-1 text-fg-muted">
                    <a href="https://github.com/LucasKovacs" target="_blank" rel="noopener" class="text-brand-400 hover:underline">lucky</a>
                    &middot;
                    <a href="https://github.com/BeReal86" target="_blank" rel="noopener" class="text-brand-400 hover:underline">BeReal</a>
                </p>
            </div>

            <div>
                <p class="font-semibold text-fg">{{ __('admin/home.hm_principal_contributors') }}</p>
                <p class="mt-1 text-fg-muted">
                    <a href="https://github.com/jonamix-ar" target="_blank" rel="noopener" class="text-brand-400 hover:underline">JonaMix</a>
                </p>
            </div>

            <div>
                <p class="font-semibold text-fg">{{ __('admin/home.hm_extensions') }}</p>
                <p class="mt-1 text-fg-muted space-x-2">
                    <a href="https://laravel.com/" target="_blank" rel="noopener" class="text-brand-400 hover:underline">Laravel</a>
                    <span>&middot;</span>
                    <a href="https://tailwindcss.com/" target="_blank" rel="noopener" class="text-brand-400 hover:underline">Tailwind CSS</a>
                    <span>&middot;</span>
                    <a href="https://alpinejs.dev/" target="_blank" rel="noopener" class="text-brand-400 hover:underline">Alpine.js</a>
                    <span>&middot;</span>
                    <a href="https://lucide.dev/" target="_blank" rel="noopener" class="text-brand-400 hover:underline">Lucide</a>
                    <span>&middot;</span>
                    <a href="https://github.com/jstar88/opbe" target="_blank" rel="noopener" class="text-brand-400 hover:underline">OPBE</a>
                </p>
            </div>

            <div>
                <p class="font-semibold text-fg">{{ __('admin/home.hm_thanks_to') }}</p>
                <p class="mt-1 text-fg-muted leading-relaxed">
                    adri93, Alberto14, angelus_ira, Anghelito, Arali, Borboco, Calzon, cyberghoser1, cyberrichy,
                    duhow, edering, Gmir17, Green, jtsamper, Kloud, LordPretender, Loucouss, medel, MSW,
                    Neko, Neurus, Nickolay, Pada, pele87, PowerMaster, privatethedawn, quaua, Razican, Tarta, Think,
                    thyphoon, tomtom, Tonique, Trojan, Saint, shoghicp, slaver7, war4head, zebulonbof, zorro2666
                </p>
            </div>
        </div>
    </x-admin.card-collapsible>
</div>
@endsection
