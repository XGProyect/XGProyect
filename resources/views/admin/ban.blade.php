@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/ban.bn_title') }}</h1>
    </div>
    <p class="mb-4 text-gray-600">{{ __('admin/ban.bn_sub_title') }}</p>

    <div class="row">

        {{-- ===== Ban a User ===== --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-slash mr-1"></i>
                        {{ __('admin/ban.bn_users_list') }}
                        <span class="badge badge-secondary ml-1">{{ $users->count() }}</span>
                    </h6>
                    <div class="sort-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary sort-btn active"
                            data-select="users-select" data-by="name" data-dir="asc">
                            <i class="fas fa-sort-alpha-down mr-1"></i>{{ __('admin/ban.bn_sort_by_user') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary sort-btn"
                            data-select="users-select" data-by="id">
                            <i class="fas fa-sort-numeric-down mr-1"></i>{{ __('admin/ban.bn_sort_by_id') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ban.form') }}" method="GET" id="form-ban-user">
                        <div class="input-group mb-3">
                            <input type="text" id="filter-users" class="form-control form-control-sm"
                                placeholder="{{ __('admin/ban.bn_filter_placeholder') }}"
                                autocomplete="off">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-filter-users">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <select name="ban_name" id="users-select" class="form-control mb-3" size="12">
                            @foreach ($users as $user)
                                <option value="{{ $user->name }}" data-id="{{ $user->id }}">{{ $user->name }} (ID: {{ $user->id }})</option>
                            @endforeach
                        </select>

                        <div class="text-center">
                            <button type="submit" name="banuser" value="1" class="btn btn-danger btn-icon-split">
                                <span class="icon text-white-50"><i class="fas fa-user-slash"></i></span>
                                <span class="text">{{ __('admin/ban.bn_button_ban') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== Lift a Ban ===== --}}
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-user-check mr-1"></i>
                        {{ __('admin/ban.bn_banned_list') }}
                        <span class="badge badge-secondary ml-1">{{ $banned_users->count() }}</span>
                    </h6>
                    <div class="sort-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary sort-btn active"
                            data-select="banned-select" data-by="name" data-dir="asc">
                            <i class="fas fa-sort-alpha-down mr-1"></i>{{ __('admin/ban.bn_sort_by_user') }}
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary sort-btn"
                            data-select="banned-select" data-by="id">
                            <i class="fas fa-sort-numeric-down mr-1"></i>{{ __('admin/ban.bn_sort_by_id') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($banned_users->isEmpty())
                        <p class="text-center text-muted my-3">{{ __('admin/ban.bn_no_banned_users') }}</p>
                    @else
                        <form action="{{ route('admin.ban.unban') }}" method="POST" id="form-unban-user">
                            @csrf
                            <div class="input-group mb-3">
                                <input type="text" id="filter-banned" class="form-control form-control-sm"
                                    placeholder="{{ __('admin/ban.bn_filter_placeholder') }}"
                                    autocomplete="off">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-filter-banned">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            <select name="unban_name" id="banned-select" class="form-control mb-3" size="12">
                                @foreach ($banned_users as $user)
                                    <option value="{{ $user->name }}" data-id="{{ $user->id }}"
                                        data-href="{{ route('admin.ban.form', ['ban_name' => $user->name]) }}">
                                        {{ $user->name }} (ID: {{ $user->id }})
                                    </option>
                                @endforeach
                            </select>

                            <div class="text-center">
                                <button type="submit" name="liftbanuser" value="1" class="btn btn-success btn-icon-split">
                                    <span class="icon text-white-50"><i class="fas fa-user-check"></i></span>
                                    <span class="text">{{ __('admin/ban.bn_button_lift_ban') }}</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // ── Sort ──────────────────────────────────────────────────────────────
    function sortSelect(select, by, dir) {
        var options = Array.prototype.slice.call(select.options);
        options.sort(function (a, b) {
            var cmp;
            if (by === 'id') {
                cmp = parseInt(a.getAttribute('data-id'), 10) - parseInt(b.getAttribute('data-id'), 10);
            } else {
                cmp = a.text.localeCompare(b.text, undefined, { sensitivity: 'base' });
            }
            return dir === 'desc' ? -cmp : cmp;
        });
        options.forEach(function (o) { select.appendChild(o); });
    }

    document.querySelectorAll('.sort-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var select = document.getElementById(this.getAttribute('data-select'));
            if (!select) return;

            var group   = this.closest('.sort-group');
            var by      = this.getAttribute('data-by');
            var current = this.getAttribute('data-dir');   // null on first click
            var next    = current === 'asc' ? 'desc' : 'asc';

            // Reset all buttons in the group, then mark this one
            group.querySelectorAll('.sort-btn').forEach(function (b) {
                b.classList.remove('active');
                b.removeAttribute('data-dir');
                // reset icon direction
                var icon = b.querySelector('i');
                if (icon) {
                    var base = b.getAttribute('data-by') === 'id' ? 'fa-sort-numeric' : 'fa-sort-alpha';
                    icon.className = 'fas ' + base + '-down mr-1';
                }
            });

            this.setAttribute('data-dir', next);
            this.classList.add('active');

            // Flip icon to reflect direction
            var icon = this.querySelector('i');
            if (icon) {
                var base = by === 'id' ? 'fa-sort-numeric' : 'fa-sort-alpha';
                icon.className = 'fas ' + base + (next === 'desc' ? '-down-alt' : '-down') + ' mr-1';
            }

            sortSelect(select, by, next);
        });
    });

    // ── Filter ────────────────────────────────────────────────────────────
    function setupFilter(inputId, clearId, selectId) {
        var input  = document.getElementById(inputId);
        var clear  = document.getElementById(clearId);
        var select = document.getElementById(selectId);
        if (!input || !select) return;

        input.addEventListener('input', function () {
            var q = this.value.toLowerCase();
            Array.prototype.forEach.call(select.options, function (o) {
                o.hidden = q !== '' && o.text.toLowerCase().indexOf(q) === -1;
            });
        });

        if (clear) {
            clear.addEventListener('click', function () {
                input.value = '';
                Array.prototype.forEach.call(select.options, function (o) { o.hidden = false; });
            });
        }
    }

    setupFilter('filter-users',  'clear-filter-users',  'users-select');
    setupFilter('filter-banned', 'clear-filter-banned', 'banned-select');

    // ── Double-click banned user → edit view ──────────────────────────────
    document.getElementById('banned-select').addEventListener('dblclick', function () {
        var selected = this.options[this.selectedIndex];
        if (selected && selected.getAttribute('data-href')) {
            window.location.href = selected.getAttribute('data-href');
        }
    });
</script>
@endpush
@endsection
