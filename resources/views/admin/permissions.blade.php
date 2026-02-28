@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <form action="" method="POST" name="changelog">
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
        <p class="mb-4">{{ __('admin/permissions.pr_sub_title') }}</p>

        @foreach ($sections_list as $item)
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow mb-4">
                    <!-- Card Header - Accordion -->
                    <a href="#collapse{{ $item['section_name'] }}" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapse{{ $item['section_name'] }}">
                        <h6 class="m-0 font-weight-bold text-primary">{{ $item['section_title'] }}</h6>
                    </a>
                    <!-- Card Content - Collapse -->
                    <div class="collapse show" id="collapse{{ $item['section_name'] }}" style="">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th width="25%"></th>
                                            @foreach ($item['roles_list'] as $role)
                                            <th width="25%" class="text-center">{{ $role['role_name'] }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item['modules_list'] as $module)
                                        <tr>
                                            <td>
                                                <a href="/admin/{{ $module['page_module'] }}">{{ $module['page_module_title'] }}</a>
                                            </td>
                                            @foreach($module['permissions_list'] as $permission)
                                            <td class="text-center">
                                                <input class="form-check-input" type="checkbox" name="{{ $permission['module'] }}[{{ $permission['role'] }}]" {{ $permission['permission_checked'] }} {{ $permission['permission_disabled'] }}>
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