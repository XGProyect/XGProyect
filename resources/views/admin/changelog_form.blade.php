@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <script src="{{ asset('assets/js/cntchar-min.js') }}" type="text/javascript"></script>
    <form action="{{ $action === 'edit' ? route('admin.changelog.update', $changelog_id) : route('admin.changelog.store') }}" method="POST" name="changelog">
        @csrf
        @if($action === 'edit')
            @method('PUT')
        @endif
        <!-- Page Heading -->
        <x-admin.page-header title="{{ __('admin/changelog.ch_title') }}" subtitle="{{ str_replace('%s', $changelog_date, __('admin/changelog.ch_' . $action . '_action')) }}">
            <x-slot name="action">
                <div class="d-flex" style="gap:.5rem;">
                    <a href="{{ route('admin.changelog') }}" class="btn btn-secondary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-chevron-left"></i></span>
                        <span class="text">{{ __('admin/changelog.ch_back') }}</span>
                    </a>
                    <button type="submit" class="btn btn-primary btn-icon-split">
                        <span class="icon text-white-50"><i class="fas fa-save"></i></span>
                        <span class="text">{{ __('admin/changelog.ch_save') }}</span>
                    </button>
                </div>
            </x-slot>
        </x-admin.page-header>

        <div class="row">
            <div class="col-lg-8">
                <x-admin.card title="{{ __('admin/changelog.ch_the_description') }}" icon="fas fa-align-left fa-xs">
                        <div class="form-group mb-0">
                            <textarea class="form-control" id="changelogText" name="text" rows="14"
                                onkeyup="javascript:cntChars('changelog', 5000);"
                                placeholder="{{ __('admin/changelog.ch_description_placeholder') }}"
                                required>{{ $changelog_description }}</textarea>
                            <small class="form-text text-muted mt-1">
                                <span id="cntChars">{{ mb_strlen($changelog_description) }}</span> / 5000 {{ __('admin/changelog.ch_characters') }}
                            </small>
                        </div>
                    </x-admin.card>
            </div>

            <div class="col-lg-4">
                <x-admin.card title="{{ __('admin/changelog.ch_entry_details') }}" icon="fas fa-info-circle fa-xs">
                        <div class="form-group">
                            <label for="changelog_date" class="font-weight-bold small text-gray-700">{{ __('admin/changelog.ch_date') }}</label>
                            <input class="form-control" type="date" id="changelog_date" name="changelog_date"
                                value="{{ $changelog_date }}" min="1000-01-01" max="3000-12-31" required>
                        </div>
                        <div class="form-group">
                            <label for="changelog_version" class="font-weight-bold small text-gray-700">
                                {{ __('admin/changelog.ch_version') }}
                                <i class="fas fa-question-circle ml-1 text-gray-400" data-toggle="popover"
                                    data-trigger="hover" data-content="{{ __('admin/changelog.ch_version_info') }}"
                                    data-html="true"></i>
                            </label>
                            <input class="form-control" type="text" id="changelog_version" name="changelog_version"
                                value="{{ $changelog_version }}" placeholder="e.g. 1.12.3"
                                pattern="^(0|[1-9]\d*)\.((0|[1-9]\d*)\.)?(0|[1-9]\d*)(-(0|[1-9]\d*|\d*[a-zA-Z][0-9a-zA-Z]*))?$"
                                required>
                        </div>
                        <div class="form-group mb-0">
                            <label for="changelog_language" class="font-weight-bold small text-gray-700">{{ __('admin/changelog.ch_language') }}</label>
                            @if($action === 'edit')
                                @php $selectedLang = collect($languages)->firstWhere('selected', 'selected'); @endphp
                                <input type="hidden" name="changelog_language" value="{{ $selectedLang['id'] ?? '' }}">
                                <input type="text" class="form-control bg-light" value="{{ $selectedLang['name'] ?? '' }}" disabled>
                                <small class="form-text text-muted">{{ __('admin/changelog.ch_language_locked') }}</small>
                            @else
                                <select class="form-control" id="changelog_language" name="changelog_language" required>
                                    <option value="">{{ __('admin/changelog.ch_pick_language') }}</option>
                                    @foreach ($languages as $item)
                                    <option value="{{ $item['id'] }}" {{ $item['selected'] }}>{{ $item['name'] }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </x-admin.card>
            </div>
        </div>
    </form>
</div>
@endsection