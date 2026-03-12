@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <x-admin.page-header
        :title="__('admin/errors.er_title')"
        :subtitle="__('admin/errors.er_sub_title')"
    >
        <x-slot name="action">
            <div class="d-flex" style="gap: .5rem;">
                <a href="{{ route('admin.errors.export') }}" class="btn btn-success btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-file-export"></i>
                    </span>
                    <span class="text">{{ __('admin/errors.er_export') }}</span>
                </a>
                <form method="POST" action="{{ route('admin.errors.delete') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-trash-alt"></i>
                        </span>
                        <span class="text">{{ __('admin/errors.er_delete_all') }}</span>
                    </button>
                </form>
            </div>
        </x-slot>
    </x-admin.page-header>

    <div class="row">
        <div class="col-lg-12">
            <x-admin.card-collapsible id="collapseErrors" :title="__('admin/errors.er_error_list')" :badge="$totalErrors">
                @forelse ($errorsList as $index => $item)
                    <div class="px-3 py-3 border-bottom {{ $loop->last ? 'border-bottom-0' : '' }}">
                        <div class="d-flex align-items-baseline mb-2">
                            <i class="fas fa-exclamation-circle text-danger mr-3 flex-shrink-0"></i>
                            <div class="flex-grow-1">
                                <code class="text-danger text-break" style="font-size: .85rem;">{{ $item['error_message'] }}</code>
                                @if ($item['count'] > 1)
                                    <span class="badge badge-danger ml-2">
                                        {{ trans_choice('admin/errors.er_occurrences', $item['count'], ['count' => $item['count']]) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if (!empty($item['errors']))
                            <pre class="ml-4 mb-0 p-3 rounded text-white" style="background: #2d2d2d; font-size: .78rem; white-space: pre-wrap; word-break: break-all;">@foreach ($item['errors'] as $error){{ $error }}
@endforeach</pre>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-check-circle fa-3x mb-3 text-success d-block"></i>
                        <strong>{{ trans_choice('admin/errors.er_errors', 0, ['count' => 0]) }}</strong>
                    </div>
                @endforelse
            </x-admin.card-collapsible>
        </div>
    </div>
</div>
@endsection