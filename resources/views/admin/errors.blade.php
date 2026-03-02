@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/errors.er_title') }}"
        subtitle="{!! __('admin/errors.er_sub_title') !!}"
    >
        <x-slot name="action">
            <div class="d-flex" style="gap: .5rem;">
                <a href="/admin/errors?exportall=yes" class="btn btn-success btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-file-export"></i>
                    </span>
                    <span class="text">{{ __('admin/errors.er_export') }}</span>
                </a>
                <a href="/admin/errors?deleteall=yes" class="btn btn-danger btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-trash-alt"></i>
                    </span>
                    <span class="text">{{ __('admin/errors.er_delete_all') }}</span>
                </a>
            </div>
        </x-slot>
    </x-admin.page-header>
    <div class="row">
        <div class="col-lg-12">
            <x-admin.card-collapsible id="collapseErrors" title="{{ __('admin/errors.er_error_list') }}">
                        <div class="table-responsive">
                            <table class="table table-borderless" width="100%" cellspacing="0">
                                <tbody>
                                @foreach ($errorsList as $item)
                                    <tr>
                                        <th>
                                            <div class="alert alert-danger" role="alert">
                                                {{ $item['error_message'] }}
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td>
                                        @foreach ($item['errors'] as $error)
                                            {{ $error }} <br>
                                        @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5">
                                            {{ trans_choice('admin/errors.er_errors', $totalErrors, ['count' => $totalErrors]) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </x-admin.card-collapsible>
        </div>
    </div>
    <div class="d-flex justify-content-end mb-4">
        <a href="/admin/errors?deleteall=yes" class="btn btn-danger btn-icon-split">
            <span class="icon text-white-50">
                <i class="fas fa-trash-alt"></i>
            </span>
            <span class="text">{{ __('admin/errors.er_delete_all') }}</span>
        </a>
    </div>
</div>
@endsection