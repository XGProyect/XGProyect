@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form name="frm_reset" action="" method="post">
        @csrf

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/reset.re_reset_h1') }}</h1>
            <button type="submit" class="btn btn-danger btn-icon-split mt-3 mt-sm-0"
                onclick="return confirm('{{ __('admin/reset.re_reset_universe_confirmation') }}');">
                <span class="icon text-white-50"><i class="fas fa-undo-alt"></i></span>
                <span class="text">{{ __('admin/reset.re_reset_go') }}</span>
            </button>
        </div>
        <p class="mb-4">{{ __('admin/reset.re_sub_title') }}</p>

        {{-- General card: full-width, multi-column --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/reset.re_general') }}</h6>
                        <div class="custom-control custom-checkbox m-0">
                            <input type="checkbox" class="custom-control-input card-select-all"
                                id="all_general" data-group="general">
                            <label class="custom-control-label" for="all_general"></label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $generalItems = [
                                    ['name' => 'moons',      'label' => __('admin/reset.re_reset_moons')],
                                    ['name' => 'notes',      'label' => __('admin/reset.re_reset_notes')],
                                    ['name' => 'rw',         'label' => __('admin/reset.re_reset_rw')],
                                    ['name' => 'friends',    'label' => __('admin/reset.re_reset_buddies')],
                                    ['name' => 'alliances',  'label' => __('admin/reset.re_reset_allys')],
                                    ['name' => 'fleets',     'label' => __('admin/reset.re_reset_fleets')],
                                    ['name' => 'banneds',    'label' => __('admin/reset.re_reset_banned')],
                                    ['name' => 'messages',   'label' => __('admin/reset.re_reset_messages')],
                                    ['name' => 'statpoints', 'label' => __('admin/reset.re_reset_statpoints')],
                                ];
                            @endphp
                            @foreach($generalItems as $item)
                            <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input reset-item"
                                        name="{{ $item['name'] }}" id="{{ $item['name'] }}" data-group="general">
                                    <label class="custom-control-label" for="{{ $item['name'] }}">{{ $item['label'] }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4 category cards --}}
        <div class="row">
            @php
                $cards = [
                    ['id' => 'buildings', 'title' => __('admin/reset.re_buldings'), 'items' => [
                        ['name' => 'edif_p', 'label' => __('admin/reset.re_buildings_pl')],
                        ['name' => 'edif_l', 'label' => __('admin/reset.re_buildings_lu')],
                        ['name' => 'edif',   'label' => __('admin/reset.re_reset_buldings')],
                    ]],
                    ['id' => 'research', 'title' => __('admin/reset.re_inve_ofis'), 'items' => [
                        ['name' => 'ofis',    'label' => __('admin/reset.re_ofici')],
                        ['name' => 'inves',   'label' => __('admin/reset.re_investigations')],
                        ['name' => 'inves_c', 'label' => __('admin/reset.re_reset_invest')],
                    ]],
                    ['id' => 'defenses', 'title' => __('admin/reset.re_defenses_and_ships'), 'items' => [
                        ['name' => 'defenses', 'label' => __('admin/reset.re_defenses')],
                        ['name' => 'ships',    'label' => __('admin/reset.re_ships')],
                        ['name' => 'h_d',      'label' => __('admin/reset.re_reset_hangar')],
                    ]],
                    ['id' => 'resources', 'title' => __('admin/reset.re_resources'), 'items' => [
                        ['name' => 'dark',      'label' => __('admin/reset.re_resources_dark')],
                        ['name' => 'resources', 'label' => __('admin/reset.re_resources_met_cry')],
                    ]],
                ];
            @endphp

            @foreach($cards as $card)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $card['title'] }}</h6>
                        <div class="custom-control custom-checkbox m-0">
                            <input type="checkbox" class="custom-control-input card-select-all"
                                id="all_{{ $card['id'] }}" data-group="{{ $card['id'] }}">
                            <label class="custom-control-label" for="all_{{ $card['id'] }}"></label>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($card['items'] as $item)
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input reset-item"
                                name="{{ $item['name'] }}" id="{{ $item['name'] }}" data-group="{{ $card['id'] }}">
                            <label class="custom-control-label" for="{{ $item['name'] }}">{{ $item['label'] }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Danger zone --}}
        <div class="row">
            <div class="col-12">
                <div class="card border-danger shadow mb-4">
                    <div class="card-header py-3 bg-danger">
                        <h6 class="m-0 font-weight-bold text-white">
                            <i class="fas fa-exclamation-triangle mr-2"></i>{{ __('admin/reset.re_reset_all') }}
                        </h6>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <p class="mb-0 text-gray-800">{{ __('admin/reset.re_reset_universe_confirmation') }}</p>
                        <div class="custom-control custom-checkbox ml-4 flex-shrink-0">
                            <input type="checkbox" class="custom-control-input" name="resetall" id="resetall">
                            <label class="custom-control-label" for="resetall"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('.card-select-all').on('change', function () {
        var group = $(this).data('group');
        $('.reset-item[data-group="' + group + '"]').prop('checked', this.checked);
    });

    $('.reset-item').on('change', function () {
        var group   = $(this).data('group');
        var total   = $('.reset-item[data-group="' + group + '"]').length;
        var checked = $('.reset-item[data-group="' + group + '"]:checked').length;
        $('#all_' + group)
            .prop('indeterminate', checked > 0 && checked < total)
            .prop('checked', checked === total);
    });
});
</script>
@endpush
