@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/navigation.nv_search_results') }}"
        subtitle="{{ $query ? __('admin/navigation.nv_search_results_for', ['query' => $query]) : __('admin/navigation.nv_search_hint') }}"
    >
        <x-slot name="action">
            <form action="{{ route('admin.search') }}" method="GET" class="form-inline">
                <div class="input-group">
                    <input type="text" name="term" value="{{ $query }}" class="form-control border"
                        placeholder="{{ __('admin/navigation.nv_search_for') }}" autofocus autocomplete="off" minlength="3">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search fa-sm mr-1"></i>{{ __('admin/navigation.nv_search_go') }}
                        </button>
                    </div>
                </div>
            </form>
        </x-slot>
    </x-admin.page-header>


    @if($query)
    <div class="card shadow mb-4">
        <div class="card-body p-0">
            @if($results->isEmpty())
                <p class="text-muted p-4 mb-0">{{ __('admin/navigation.nv_search_no_results') }}</p>
            @else
                <table class="table table-borderless table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('admin/navigation.nv_search_col_name') }}</th>
                            <th>{{ __('admin/navigation.nv_search_col_type') }}</th>
                            <th>{{ __('admin/navigation.nv_search_col_detail') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                        <tr>
                            <td class="align-middle font-weight-bold">{{ $result['label'] }}</td>
                            <td class="align-middle">
                                @php
                                    $badge = match($result['type']) {
                                        'User'     => 'primary',
                                        'Alliance' => 'success',
                                        'Planet'   => 'info',
                                        'Moon'     => 'secondary',
                                        default    => 'light',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ $result['type'] }}</span>
                            </td>
                            <td class="align-middle text-muted small">{{ $result['detail'] }}</td>
                            <td class="align-middle text-right">
                                <a href="{{ $result['url'] }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-arrow-right fa-xs"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
