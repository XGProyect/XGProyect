@extends('master.install')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-0 text-gray-800">{{ __('install/install.requirements') }}</h1>
    <hr class="sidebar-divider d-none d-md-block">
    <p class="mb-4">{!! __('install/install.requirements_details') !!}</p>

    <div class="row">
        @foreach ($testResults as $test)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-{{ $test['result'] }} shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $test['result'] }} text-uppercase mb-1">
                                {{ $test['requirement'] }}
                            </div>
                            <div class="h6 mb-0 font-weight-bold text-gray-800">
                                {{ $test['message'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="#" class="btn btn-{{ $test['result'] }} btn-circle btn-md">
                                @if ($test['result'] === 'success')
                                <i class="fas fa-check"></i>
                                @elseif($test['result'] === 'warning')
                                <i class="fas fa-exclamation"></i>
                                @else
                                <i class="fas fa-times"></i>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card mb-4 py-3 border-bottom-primary">
        <div class="card-body text-center">
            <p>{{ __('install/install.requirements_notice') }}</p>
            @if ($fail)
            <p>{{ __('install/install.requirements_fail') }}</p>
            @else
            <a href="{{ route('install.step.database') }}" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-fw fa-database"></i>
                </span>
                <span class="text">{{ __('install/install.database') }}</span>
            </a>
            @endif
        </div>
    </div>
</div>
@endsection