{{--
    Shared header for the alliances module detail views.
    Required variables:
        $alliance  – Alliance model instance
        $active    – string: 'info' | 'ranks' | 'members'
--}}
<p class="mb-3 text-gray-600">{{ __('admin/alliances.al_sub_title') }}</p>

{{-- Compact search bar so the user can jump to another alliance without going back --}}
<form action="{{ route('admin.alliances') }}" method="GET" class="mb-3">
    <div class="input-group input-group-sm" style="max-width: 400px;">
        <input type="text" name="alliance" class="form-control border"
            style="box-shadow: 0 1px 4px rgba(0,0,0,.12);"
            placeholder="{{ __('admin/alliances.al_alliance_placeholder') }}"
            value="" autocomplete="off" minlength="3" required>
        <div class="input-group-append">
            <button class="btn btn-secondary" type="submit">
                <i class="fas fa-search fa-sm"></i>
            </button>
        </div>
    </div>
</form>

{{-- Tab navigation --}}
<div class="mb-4 d-flex flex-wrap" style="gap: 0.5rem;">
    <a class="btn btn-icon-split {{ $active === 'info' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.alliances.info', $alliance->alliance_id) }}">
        <span class="icon text-white-50"><i class="fas fa-info-circle"></i></span>
        <span class="text">{{ __('admin/alliances.al_general_info') }}</span>
    </a>
    <a class="btn btn-icon-split {{ $active === 'ranks' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.alliances.ranks', $alliance->alliance_id) }}">
        <span class="icon text-white-50"><i class="fas fa-sitemap"></i></span>
        <span class="text">{{ __('admin/alliances.al_ranks') }}</span>
    </a>
    <a class="btn btn-icon-split {{ $active === 'members' ? 'btn-primary' : 'btn-secondary' }}"
        href="{{ route('admin.alliances.members', $alliance->alliance_id) }}">
        <span class="icon text-white-50"><i class="fas fa-users"></i></span>
        <span class="text">{{ __('admin/alliances.al_members') }}</span>
    </a>
</div>
