@extends('master.install')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-0 text-gray-800">{{ __('install/install.welcome') }}</h1>
    <hr class="sidebar-divider d-none d-md-block">
    <p class="mb-4">{!! __('install/install.introduction') !!}</p>

    <div class="card shadow mb-4">
        <!-- Card Header - Accordion -->
        <a href="#collapseLicense" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseLicense">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('install/install.license') }}</h6>
        </a>
        <!-- Card Content - Collapse -->
        <div class="collapse show" id="collapseLicense">
            <div class="card-body">
                <textarea rows="20" readonly class="form-control">
                    {{ $license }}
                </textarea>
                <br>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="accept">
                    <label class="form-check-label" for="accept">
                        <em>{{ __('install/install.accept') }}</em>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="req-continue" class="card mb-4 py-3 border-bottom-primary" style="display: none">
        <div class="card-body text-center">
            <p>{{ __('install/install.index_notice') }}</p>
            <a href="{{ route('install.step.requirements') }}" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-fw fa-tasks"></i>
                </span>
                <span class="text">{{ __('install/install.requirements') }}</span>
            </a>
        </div>
    </div>
</div>
@endsection