@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/languages.le_edit') }}</h1>
        <button type="submit" class="btn btn-primary btn-icon-split" onClick="return confirm('{{ __('admin/languages.le_warning') }} {{ __('admin/languages.le_sure') }}') ? edit_language.submit() : false;">
            <span class="icon text-white-50">
                <i class="fas fa-save"></i>
            </span>
            <span class="text">{{ __('admin/languages.le_save_changes') }}</span>
        </button>
    </div>
    <p class="mb-4">{{ __('admin/languages.le_notice') }}</p>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseGeneral">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/languages.le_edit') }}</h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="collapseGeneral" style="">
                    <div class="card-body">
                        <form action="" method="POST" name="change_language">
                            @csrf
                            <select class="form-control" name="file" onchange="submit()">
                                <option value="">{{ __('admin/languages.le_file') }}</option>
                                @foreach ($language_files as $item)
                                <option value="{{ $item['lang_file'] }}" {{ $item['selected'] }}>{{ $item['lang_file'] }}</option>
                                @endforeach
                            </select>
                        </form>
                        <form action="" method="POST" name="edit_language">
                            @csrf
                            <input type="hidden" name="file" value="{{ $editFile }}">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <th>
                                                <p class="text-danger">{{ __('admin/languages.le_warning') }}</p>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>
                                                <textarea class="form-control" name="save" rows="20"
                                                    class="field span12">{{ $contents }}</textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-primary btn-icon-split"
                                    onClick="return confirm('{{ __('admin/languages.le_warning') }} {{ __('admin/languages.le_sure') }}')">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-save"></i>
                                    </span>
                                    <span class="text">{{ __('admin/languages.le_save_changes') }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection