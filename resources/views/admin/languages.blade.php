@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <x-admin.page-header
        title="{{ __('admin/languages.le_edit') }}"
        subtitle="{{ __('admin/languages.le_notice') }}"
    >
        @if ($currentFile)
        <x-slot name="action">
            <button type="submit" form="translationsForm" class="btn btn-primary btn-icon-split mt-3 mt-sm-0">
                <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                <span class="text">{{ __('admin/languages.le_save_changes') }}</span>
            </button>
        </x-slot>
        @endif
    </x-admin.page-header>

    <div class="row">

        <!-- ── LEFT SIDEBAR: module → file tree ───────────────────────────── -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow">
                <a href="#collapseFileTree" class="d-block card-header py-3" data-toggle="collapse"
                   role="button" aria-expanded="true" aria-controls="collapseFileTree">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-folder-open mr-1"></i>
                        {{ __('admin/languages.le_file') }}
                    </h6>
                </a>
                <div class="collapse show" id="collapseFileTree">

                <!-- Filter -->
                <div class="px-3 pt-3 pb-2 border-bottom">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-filter"></i></span>
                        </div>
                        <input id="langFilter"
                               type="text"
                               class="form-control"
                               placeholder="{{ __('admin/languages.le_filter_placeholder') }}"
                               autocomplete="off">
                        <div class="input-group-append">
                            <button id="langFilterClear"
                                    class="btn btn-outline-secondary d-none"
                                    type="button"
                                    title="{{ __('admin/languages.le_filter_clear') }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tree: module → file (2 levels) -->
                <div id="langBrowser" style="overflow-y:auto; max-height:70vh;">
                    @php
                        $currentGroup    = $currentFile
                            ? implode('/', array_slice(explode('/', $currentFile), 1, -1))
                            : null;
                        $currentFilename = $currentFile ? basename($currentFile) : null;
                        $currentLocale   = $currentFile ? explode('/', $currentFile)[0] : null;
                    @endphp

                    @forelse ($groupedFiles as $group => $filesByName)
                        @php
                            $groupLabel     = $group ?: __('admin/languages.le_group_general');
                            $moduleId       = 'mod-' . str_replace('/', '-', $group ?: 'general');
                            $isActiveModule = ($group === $currentGroup);
                        @endphp

                        <div class="module-block border-bottom" data-module="{{ strtolower($groupLabel) }}">

                            {{-- MODULE ROW --}}
                            <div class="module-row d-flex align-items-center px-3 py-2"
                                 style="cursor:pointer; user-select:none"
                                 data-toggle="collapse"
                                 data-target="#{{ $moduleId }}"
                                 aria-expanded="{{ $isActiveModule ? 'true' : 'false' }}"
                                 aria-controls="{{ $moduleId }}">
                                <i class="fas fa-chevron-right module-icon text-gray-400 mr-2" style="font-size:.75rem"></i>
                                <strong class="flex-grow-1 module-label">{{ ucfirst($groupLabel) }}</strong>
                                <span class="badge badge-light border">{{ count($filesByName) }}</span>
                            </div>

                            {{-- FILE LIST --}}
                            <div id="{{ $moduleId }}" class="collapse {{ $isActiveModule ? 'show' : '' }}">
                                @foreach ($filesByName as $filename => $locales)
                                    @php
                                        $basename     = pathinfo($filename, PATHINFO_FILENAME);
                                        $isActiveFile = ($filename === $currentFilename && $isActiveModule);
                                        // Link to current locale if revisiting, else first available locale
                                        $targetPath   = ($isActiveFile && $currentFile)
                                            ? $currentFile
                                            : (isset($locales[$currentLocale]) ? $locales[$currentLocale] : reset($locales));
                                    @endphp
                                    <div class="file-block" data-file="{{ strtolower($basename) }}">
                                        <a href="{{ route('admin.languages', ['file' => $targetPath]) }}"
                                           class="d-block px-4 py-1 border-top text-decoration-none {{ $isActiveFile ? 'bg-primary text-white font-weight-bold' : 'text-body' }}">
                                            <span class="file-label">{{ ucfirst($basename) }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @empty
                        <p class="text-muted small px-3 py-2">{{ __('admin/languages.le_no_results') }}</p>
                    @endforelse

                    <div id="langNoResults" class="d-none text-center text-muted small py-3">
                        <i class="fas fa-search d-block mb-1"></i>
                        {{ __('admin/languages.le_no_results') }}
                    </div>
                </div>{{-- #langBrowser --}}
                </div>{{-- #collapseFileTree --}}
            </div>{{-- .card --}}
        </div>

        <!-- ── RIGHT: key-value editor ────────────────────────────────────── -->
        <div class="col-lg-9 mb-4">
            @if ($currentFile)
            @php
                // Collect sibling locales for this file to show in the header
                $siblingLocales = [];
                foreach ($groupedFiles as $grp => $filesByName) {
                    foreach ($filesByName as $fname => $locales) {
                        if ($fname === $currentFilename && $grp === $currentGroup) {
                            $siblingLocales = $locales;
                            break 2;
                        }
                    }
                }
            @endphp
            <form id="translationsForm" action="{{ route('admin.languages.update') }}" method="POST">
                @csrf
                <input type="hidden" name="file" value="{{ $currentFile }}">
                <div class="card shadow">
                    <a href="#collapseEditor" class="d-block card-header py-3" data-toggle="collapse"
                       role="button" aria-expanded="true" aria-controls="collapseEditor">
                        <h6 class="m-0 font-weight-bold text-primary">
                            {{ $currentFile }}
                            <span class="badge badge-secondary ml-1">{{ count($translations) }}</span>
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

                        {{-- Locale switcher --}}
                        <div class="mb-3">
                            @foreach ($siblingLocales as $locale => $path)
                                <a href="{{ route('admin.languages', ['file' => $path]) }}"
                                   class="btn btn-sm {{ $path === $currentFile ? 'btn-primary' : 'btn-outline-secondary' }}"
                                   title="{{ $path }}">
                                    {{ strtoupper($locale) }}
                                </a>
                            @endforeach
                        </div>

                        <div id="translationsList">
                            @foreach ($translations as $i => $entry)
                                @php
                                    $dotPos   = strrpos($entry['key'], '.');
                                    $keyGroup = $dotPos !== false ? substr($entry['key'], 0, $dotPos) : null;
                                    $isNested = $keyGroup !== null;
                                    $isLong   = strlen($entry['value']) > 80 || str_contains($entry['value'], "\n");
                                    $rows     = $isLong ? min(max(substr_count($entry['value'], "\n") + 2, 3), 8) : 0;
                                    $inputId  = 'trans_' . $i;
                                @endphp
                                <div class="form-group translation-row" data-key="{{ $entry['key'] }}">
                                    <label for="{{ $inputId }}" class="mb-1">
                                        <input type="hidden"
                                               name="translations[{{ $i }}][key]"
                                               value="{{ $entry['key'] }}">
                                        @if ($isNested)
                                            <span class="text-muted">{{ $keyGroup }}.</span><code>{{ substr($entry['key'], strlen($keyGroup) + 1) }}</code>
                                        @else
                                            <code>{{ $entry['key'] }}</code>
                                        @endif
                                    </label>
                                    @if ($isLong)
                                        <textarea id="{{ $inputId }}"
                                                  name="translations[{{ $i }}][value]"
                                                  class="form-control"
                                                  rows="{{ $rows }}"
                                                  style="resize:vertical">{{ $entry['value'] }}</textarea>
                                    @else
                                        <input id="{{ $inputId }}"
                                               type="text"
                                               name="translations[{{ $i }}][value]"
                                               class="form-control"
                                               value="{{ $entry['value'] }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>{{-- .card-body --}}
                    </div>{{-- #collapseEditor --}}
                </div>{{-- .card --}}
            </form>
            @else
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt fa-xs mr-2"></i>{{ __('admin/languages.le_file') }}
                    </h6>
                </div>
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-hand-point-left fa-2x mb-3 d-block"></i>
                    {{ __('admin/languages.le_file') }}
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    /* ── module chevron sync with Bootstrap collapse ────────────────────── */
    $(document).on('show.bs.collapse', '.module-block > .collapse', function () {
        $('[data-target="#' + this.id + '"]').attr('aria-expanded', 'true');
    });
    $(document).on('hide.bs.collapse', '.module-block > .collapse', function () {
        $('[data-target="#' + this.id + '"]').attr('aria-expanded', 'false');
    });

    /* ── live tree filter ───────────────────────────────────────────────── */
    var $filter   = $('#langFilter');
    var $clearBtn = $('#langFilterClear');
    var $noRes    = $('#langNoResults');

    function applyFilter(q) {
        q = q.trim().toLowerCase();
        var anyVisible = false;

        $('.module-block').each(function () {
            var $module  = $(this);
            var modLabel = $module.data('module');
            var modMatch = !q || modLabel.indexOf(q) >= 0;
            var fileHit  = false;

            $module.find('.file-block').each(function () {
                var $fb  = $(this);
                var show = modMatch || $fb.data('file').indexOf(q) >= 0;
                $fb.toggleClass('d-none', !show);
                if (show) fileHit = true;
            });

            var showMod = !q || modMatch || fileHit;
            $module.toggleClass('d-none', !showMod);
            if (showMod) anyVisible = true;

            if (q && (modMatch || fileHit)) {
                $module.find('> .collapse').collapse('show');
            }
        });

        $noRes.toggleClass('d-none', anyVisible);
        $clearBtn.toggleClass('d-none', !q);
        q ? highlightText(q) : removeHighlight();
    }

    function highlightText(q) {
        removeHighlight();
        $('.module-label, .file-label').each(function () {
            var text = $(this).text();
            var idx  = text.toLowerCase().indexOf(q);
            if (idx >= 0) {
                $(this).html(
                    text.substring(0, idx)
                    + '<mark>' + text.substring(idx, idx + q.length) + '</mark>'
                    + text.substring(idx + q.length)
                );
            }
        });
    }

    function removeHighlight() {
        $('.module-label, .file-label').each(function () { $(this).text($(this).text()); });
    }

    $filter.on('input', function () { applyFilter($(this).val()); });
    $clearBtn.on('click', function () { $filter.val('').trigger('input').focus(); });

    /* ── key/value search in editor ─────────────────────────────────────── */
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
