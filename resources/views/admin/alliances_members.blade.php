<div class="card shadow mb-4">
    <!-- Card Header - Accordion -->
    <a href="#collapseMembers" class="d-block card-header py-3" data-toggle="collapse" role="button"
        aria-expanded="true" aria-controls="collapseMembers">
        <h6 class="m-0 font-weight-bold text-primary">{{ $al_alliance_members }}</h6>
    </a>
    <!-- Card Content - Collapse -->
    <div class="collapse show" id="collapseMembers" style="">
        <div class="card-body">
            <div class="table-responsive">
                <form name="save_ranks" method="post" action="">
                    @csrf
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="checkall" id="checkall">
                            </td>
                            <th>{{ __('admin/alliances.al_alliance_username') }}</th>
                            <th>{{ __('admin/alliances.al_alliance_pending_request') }}</th>
                            <th>{{ __('admin/alliances.al_alliance_request_text') }}</th>
                            <th>{{ __('admin/alliances.al_inscription_date') }}</th>
                            <th>{{ __('admin/alliances.al_alliance_member_rank') }}</th>
                        </tr>
                        {!! $members_table !!}
                    </table>
                    <div class="text-center">
                        <button type="submit" name="delete_members" class="btn btn-danger btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-trash-alt"></i>
                            </span>
                            <span class="text">{{ __('admin/alliances.al_delete_members') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>