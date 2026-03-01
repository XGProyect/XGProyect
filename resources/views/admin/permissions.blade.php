@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST" name="permissions">
        @csrf
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/permissions.pr_title') }}</h1>
            <button type="submit" class="btn btn-primary btn-icon-split">
                <span class="icon text-white-50">
                    <i class="fas fa-save"></i>
                </span>
                <span class="text">{{ __('admin/permissions.pr_save_all') }}</span>
            </button>
        </div>
        <p class="mb-4 text-gray-600">{{ __('admin/permissions.pr_sub_title') }}</p>

        @foreach ($sections_list as $item)
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <!-- Card Header - Accordion -->
                    <a href="#collapse{{ $item['section_name'] }}" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapse{{ $item['section_name'] }}">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-chevron-down fa-xs mr-2 collapse-icon"></i>
                            {{ $item['section_title'] }}
                        </h6>
                    </a>
                    <!-- Card Content - Collapse -->
                    <div class="collapse show" id="collapse{{ $item['section_name'] }}">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="pl-4 align-middle" style="width:40%;">{{ __('admin/permissions.pr_module') }}</th>
                                            @foreach ($item['roles_list'] as $roleId => $role)
                                            <th class="text-center align-middle" style="width:20%;">
                                                @if ($roleId === \Xgp\App\Core\Enumerators\UserRanksEnumerator::ADMIN)
                                                    <i class="fas fa-lock fa-xs mr-1 text-muted"></i><span class="text-muted">{{ $role['role_name'] }}</span>
                                                @else
                                                    {{ $role['role_name'] }}
                                                @endif
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item['modules_list'] as $module)
                                        <tr>
                                            <td class="pl-4 align-middle">
                                                <a href="/admin/{{ $module['page_module'] }}" class="text-gray-800 font-weight-bold">
                                                    <i class="fas fa-angle-right fa-xs mr-1 text-primary"></i>
                                                    {{ $module['page_module_title'] }}
                                                </a>
                                            </td>
                                            @foreach($module['permissions_list'] as $permission)
                                            <td class="text-center align-middle">
                                                @if($permission['permission_disabled'])
                                                    <span class="text-success" title="{{ __('admin/permissions.pr_always_allowed') }}">
                                                        <i class="fas fa-check-circle fa-lg"></i>
                                                    </span>
                                                @else
                                                <div class="custom-control custom-switch d-inline-block">
                                                    <input type="checkbox"
                                                        class="custom-control-input"
                                                        id="perm_{{ $permission['module'] }}_{{ $permission['role'] }}"
                                                        name="{{ $permission['module'] }}[{{ $permission['role'] }}]"
                                                        {{ $permission['permission_checked'] }}>
                                                    <label class="custom-control-label" for="perm_{{ $permission['module'] }}_{{ $permission['role'] }}"></label>
                                                </div>
                                                @endif
                                            </td>
                                            @endforeach
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
        @endforeach
    </form>
</div>
@endsection