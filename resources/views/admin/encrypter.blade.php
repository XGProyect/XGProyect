@extends('master.admin')

@section('content')
<div class="container-fluid">
    <form name="frm_encrypter" method="POST" action="{{ route('admin.encrypter.encrypt') }}">
        @csrf
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/encrypter.et_title') }}</h1>
        </div>
        <p class="mb-4">{{ __('admin/encrypter.et_sub_title') }}</p>

        <div class="row justify-content-center">
            <div class="col-xl-8 col-lg-10">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/encrypter.et_general') }}</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <input class="form-control" type="text" name="unencrypted"
                                value="{{ $unencrypted }}" placeholder="{{ __('admin/encrypter.et_pass') }}">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text" onclick="navigator.clipboard.writeText($('#encrypted').val());" style="cursor: pointer">
                                    <i class="fas fa-copy"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="encrypted" name="encrypted" value="{{ $encrypted }}" disabled>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-icon-split">
                                <span class="icon text-white-50">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span class="text">{{ __('admin/encrypter.et_encript') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection