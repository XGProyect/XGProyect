@extends('master.admin')

@section('content')
<div class="container-fluid">
    <x-alert/>
    <x-admin.page-header
        title="{{ __('admin/rebuildhighscores.sb_title') }}"
        subtitle="{{ __('admin/rebuildhighscores.sb_sub_title') }}"
    >
        <x-slot name="action">
            <form method="POST" action="{{ route('admin.rebuildhighscores.run') }}">
                @csrf
                <button type="submit" class="btn btn-primary btn-icon-split">
                    <span class="icon text-white-50"><i class="fas fa-sync-alt"></i></span>
                    <span class="text">{{ __('admin/rebuildhighscores.sb_rebuild') }}</span>
                </button>
            </form>
        </x-slot>
    </x-admin.page-header>

    @if(session('memory_p'))
    <div class="row">
        <div class="col-lg-6">
            <x-admin.card-collapsible id="collapseGeneral" title="{{ __('admin/rebuildhighscores.sb_stats_updated') }}">
                        <div class="table-responsive">
                            <table class="table table-borderless" width="100%" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td><span>{{ __('admin/rebuildhighscores.sb_top_memory') }}</span></td>
                                        <td>{{ session('memory_p') }}</td>
                                    </tr>
                                    <tr>
                                        <td><span>{{ __('admin/rebuildhighscores.sb_start_memory') }}</span></td>
                                        <td>{{ session('memory_i') }}</td>
                                    </tr>
                                    <tr>
                                        <td><span>{{ __('admin/rebuildhighscores.sb_final_memory') }}</span></td>
                                        <td>{{ session('memory_e') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
            </x-admin.card-collapsible>
        </div>
        <div class="col-lg-6">
        </div>
    </div>
    @endif
</div>
@endsection