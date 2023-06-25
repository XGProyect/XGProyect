@extends('master.install')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <h1 class="h3 mb-0 text-gray-800">{{ __('install/install.tables') }}</h1>
    <hr class="sidebar-divider d-none d-md-block">
    <p class="mb-4">{!! __('install/install.tables_details') !!}</p>

    <div class="card shadow mb-4">
        <!-- Card Header - Accordion -->
        <a href="#collapseRecords" class="d-block card-header py-3 collapsed" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseRecords">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('install/install.results') }}</h6>
        </a>
        <!-- Card Content - Collapse -->
        <div class="collapse" id="collapseRecords">
            <div class="card-body">
                @forelse ($results as $item => $result)
                <div>
                    <p>
                        <span class="icon text-success">
                            <i class="fas fa-fw fa-check-circle"></i>
                        </span>
                        <strong>{{ __('install/install.' . $item) }}</strong>
                        <em>
                            {!! $result !!}
                        </em>
                    </p>
                </div>
                @empty
                <div>
                    <p>
                        {{ __('install/install.no_logs') }}
                    </p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    @if ($installed)
    <div class="card mb-4 py-3 border-bottom-primary">
        <div class="card-body text-center">
            <p>{{ __('install/install.tables_notice') }}</p>
            <a href="{{ route('install.step.admin') }}" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-fw fa-user-shield"></i>
                </span>
                <span class="text">{{ __('install/install.admin') }}</span>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection