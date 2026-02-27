@extends('master.admin')

@section('content')
<div class="container-fluid">
    <form name="frm_encrypter" method="POST" action="">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">{{ __('admin/encrypter.et_title') }}</h1>
        </div>
        <p class="mb-4">{{ __('admin/encrypter.et_sub_title') }}</p>

        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <!-- Card Header - Accordion -->
                    <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                        aria-expanded="true" aria-controls="collapseGeneral">
                        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/encrypter.et_general') }}</h6>
                    </a>
                    <!-- Card Content - Collapse -->
                    <div class="collapse show" id="collapseGeneral" style="">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless" width="100%" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <input class="form-control" type="text" name="unencrypted"
                                                    value="{{ $unencrypted }}" placeholder="{{ __('admin/encrypter.et_pass') }}">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text" onclick="navigator.clipboard.writeText($('#encrypted').val());" style="cursor: pointer">
                                                            <i class="fas fa-copy"></i>
                                                        </span>
                                                    </div>
                                                    <input type="text" class="form-control" id="encrypted" name="encrypted" value="{{ $encrypted }}" disabled>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
            </div>
            <div class="col-lg-6">
            </div>
        </div>
    </form>
</div>
@endsection