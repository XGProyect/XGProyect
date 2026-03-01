{{--
    Shared header for the users module detail views.
    Required variables:
        $user   – User model instance
        $active – string: 'info' | 'settings' | 'research' | 'premium' | 'planets' | 'moons'
--}}
<p class="mb-3 text-gray-600">{{ __('admin/users.us_sub_title') }}</p>

{{-- Compact search bar so the user can jump to another user without going back --}}
<form action="{{ route('admin.users') }}" method="GET" class="mb-3">
    <div class="input-group input-group-sm" style="max-width: 400px;">
        <input type="text" name="user" class="form-control border"
            style="box-shadow: 0 1px 4px rgba(0,0,0,.12);"
            placeholder="{{ __('admin/users.us_username_placeholder') }}"
            value="" autocomplete="off" required>
        <div class="input-group-append">
            <button class="btn btn-secondary" type="submit">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
    </div>
</form>

{{-- Tab navigation --}}
<div class="mb-4 d-flex flex-wrap" style="gap: 0.5rem;">
    <a class="btn btn-icon-split btn-sm {{ $active === 'info' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.users.info', $user->id) }}">
        <span class="icon text-white-50"><i class="fas fa-user"></i></span>
        <span class="text">{{ __('admin/users.us_tab_info') }}</span>
    </a>
    <a class="btn btn-icon-split btn-sm {{ $active === 'settings' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.users.settings', $user->id) }}">
        <span class="icon text-white-50"><i class="fas fa-cog"></i></span>
        <span class="text">{{ __('admin/users.us_tab_settings') }}</span>
    </a>
    <a class="btn btn-icon-split btn-sm {{ $active === 'research' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.users.research', $user->id) }}">
        <span class="icon text-white-50"><i class="fas fa-flask"></i></span>
        <span class="text">{{ __('admin/users.us_tab_research') }}</span>
    </a>
    <a class="btn btn-icon-split btn-sm {{ $active === 'premium' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.users.premium', $user->id) }}">
        <span class="icon text-white-50"><i class="fas fa-star"></i></span>
        <span class="text">{{ __('admin/users.us_tab_premium') }}</span>
    </a>
    <a class="btn btn-icon-split btn-sm {{ $active === 'planets' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.users.planets', $user->id) }}">
        <span class="icon text-white-50"><i class="fas fa-globe"></i></span>
        <span class="text">{{ __('admin/users.us_tab_planets') }}</span>
    </a>
    <a class="btn btn-icon-split btn-sm {{ $active === 'moons' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.users.moons', $user->id) }}">
        <span class="icon text-white-50"><i class="fas fa-moon"></i></span>
        <span class="text">{{ __('admin/users.us_tab_moons') }}</span>
    </a>
</div>
