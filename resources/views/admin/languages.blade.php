@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/languages.le_edit') }}</h1>
        @if ($currentFile)
        <button type="submit" form="translationsForm" class="btn btn-primary btn-icon-split mt-3 mt-sm-0">
            <span class="icon text-white-50"><i class="fas fa-save"></i></span>
            <span class="text">{{ __('admin/languages.le_save_changes') }}</span>
        </button>
        @endif
    </div>
    <p class="mb-4">{{ __('admin/languages.le_notice') }}</p>

    <!-- File selector -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <a href="#collapseFile" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseFile">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/languages.le_file') }}</h6>
                </a>
                <div class="collapse show" id="collapseFile">
                    <div class="card-body">
                        <form action="{{ route('admin.languages') }}" method="GET">
                            <div class="input-group">
                                <select class="form-control" name="file" onchange="this.form.submit()">
                                    <option value="">— {{ __('admin/languages.le_file') }} —</option>
                                    @foreach ($language_files as $file)
                                        <option value="{{ $file }}" @selected($file === $currentFile)>{{ $file }}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($currentFile)
    <!-- Key-value editor -->
    <form id="translationsForm" action="{{ route('admin.languages.update') }}" method="POST">
        @csrf
        <input type="hidden" name="file" value="{{ $currentFile }}">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <a href="#collapseEditor" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapseEditor">
                        <h6 class="m-0 font-weight-bold text-primary">
                            {{ $currentFile }}
                            <span class="badge badge-secondary ml-2">{{ count($translations) }}</span>
                        </h6>
                    </a>
                    <div class="collapse show" id="collapseEditor">
                        <div class="card-body">
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    </div>
                                    <input type="text" id="searchKeys" class="form-control"
                                           placeholder="{{ __('admin/languages.le_search_placeholder') }}"
                                           autocomplete="off">
                                </div>
                                <small id="matchCount" class="form-text text-muted"></small>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th width="30%">{{ __('admin/languages.le_col_key') }}</th>
                                            <th>{{ __('admin/languages.le_col_value') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($translations as $i => $entry)
                                            @php
                                                $dotPos   = strrpos($entry['key'], '.');
                                                $group    = $dotPos !== false ? substr($entry['key'], 0, $dotPos) : null;
                                                $isNested = $group !== null;
                                                $isLong   = strlen($entry['value']) > 80 || str_contains($entry['value'], "\n");
                                                $rows     = $isLong ? min(max(substr_count($entry['value'], "\n") + 2, 3), 8) : 0;
                                            @endphp
                                            <tr class="translation-row" data-key="{{ $entry['key'] }}">
                                                <td class="align-middle">
                                                    <input type="hidden"
                                                           name="translations[{{ $i }}][key]"
                                                           value="{{ $entry['key'] }}">
                                                    @if ($isNested)
                                                        <small class="text-muted">{{ $group }}.</small><code>{{ substr($entry['key'], strlen($group) + 1) }}</code>
                                                    @else
                                                        <code>{{ $entry['key'] }}</code>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    @if ($isLong)
                                                        <textarea name="translations[{{ $i }}][value]"
                                                                  class="form-control"
                                                                  rows="{{ $rows }}"
                                                                  style="resize:vertical">{{ $entry['value'] }}</textarea>
                                                    @else
                                                        <input type="text"
                                                               name="translations[{{ $i }}][value]"
                                                               class="form-control"
                                                               value="{{ $entry['value'] }}">
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var $rows    = $('.translation-row');
    var $counter = $('#matchCount');

    $('#searchKeys').on('input', function () {
        var q    = $(this).val().trim().toLowerCase();
        var hits = 0;

        $rows.each(function () {
            var key   = $(this).data('key').toLowerCase();
            var val   = $(this).find('input[type=text], textarea').val().toLowerCase();
            var match = !q || key.indexOf(q) >= 0 || val.indexOf(q) >= 0;
            $(this).toggle(match);
            if (match) hits++;
        });

        $counter.text(q ? (hits + ' / ' + $rows.length + ' keys') : '');
    });
});
</script>
@endpush
