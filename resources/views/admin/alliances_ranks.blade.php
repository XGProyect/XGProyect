<div class="card shadow mb-4">
    <!-- Card Header - Accordion -->
    <a href="#collapseRanks" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true"
        aria-controls="collapseRanks">
        <h6 class="m-0 font-weight-bold text-primary">{{ $al_alliance_ranks }}</h6>
    </a>
    <!-- Card Content - Collapse -->
    <div class="collapse show" id="collapseRanks" style="">
        <div class="card-body">
            <div class="table-responsive">
                <form name="save_ranks" method="post" action="">
                    @csrf
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <th colspan="11">{{ __('admin/alliances.al_configure_ranks') }}</th>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input" name="checkall" id="checkall">
                            </td>
                            <th>{{ __('admin/alliances.al_rank_name') }}</th>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r1.png') }}" alt="{{ __('admin/alliances.al_rank_delete_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_delete_alliance') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r2.png') }}" alt="{{ __('admin/alliances.al_rank_kick_members') }}"
                                    title="{{ __('admin/alliances.al_rank_kick_members') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r3.png') }}" alt="{{ __('admin/alliances.al_rank_see_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_see_requests') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r4.png') }}" alt="{{ __('admin/alliances.al_rank_see_memberslist') }}"
                                    title="{{ __('admin/alliances.al_rank_see_memberslist') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r5.png') }}" alt="{{ __('admin/alliances.al_rank_check_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_check_requests') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r6.png') }}" alt="{{ __('admin/alliances.al_rank_manage_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_manage_alliance') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r7.png') }}"
                                    alt="{{ __('admin/alliances.al_rank_see_online_members') }}" title="{{ __('admin/alliances.al_rank_see_online_members') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r8.png') }}" alt="{{ __('admin/alliances.al_rank_create_circular') }}"
                                    title="{{ __('admin/alliances.al_rank_create_circular') }}">
                            </td>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r9.png') }}" alt="{{ __('admin/alliances.al_rank_right_hand') }}"
                                    title="{{ __('admin/alliances.al_rank_right_hand') }}">
                            </td>
                        </tr>
                        {!! $ranks_table !!}
                        <tr>
                            <td colspan="11">
                                <div class="text-center">
                                    <button type="submit" name="save_ranks" class="btn btn-primary btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-save"></i>
                                        </span>
                                        <span class="text">{{ __('admin/alliances.al_save_ranks') }}</span>
                                    </button>
                                    <button type="submit" name="delete_ranks" class="btn btn-danger btn-icon-split">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-trash-alt"></i>
                                        </span>
                                        <span class="text">{{ __('admin/alliances.al_delete_ranks') }}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <th colspan="2">{{ __('admin/alliances.al_create_ranks') }}</th>
                        </tr>
                        <tr>
                            <td class="text-center">{{ __('admin/alliances.al_rank_name') }}</td>
                            <td class="text-center">
                                <input type="text" name="rank_name" class="form-control">
                            </td>
                            <td colspan="2" class="text-center">
                                <button type="submit" name="create_rank" class="btn btn-primary btn-icon-split">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-save"></i>
                                    </span>
                                    <span class="text">{{ __('admin/alliances.al_create_ranks') }}</span>
                                </button>
                            </td>
                        </tr>
                    </table>
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <th colspan="2">{{ __('admin/alliances.al_ranks_leyend') }}</th>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r1.png') }}" alt="{{ __('admin/alliances.al_rank_delete_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_delete_alliance') }}">
                            </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_delete_alliance') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r2.png') }}" alt="{{ __('admin/alliances.al_rank_kick_members') }}"
                                    title="{{ __('admin/alliances.al_rank_kick_members') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_kick_members') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r3.png') }}" alt="{{ __('admin/alliances.al_rank_see_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_see_requests') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_see_requests') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r4.png') }}" alt="{{ __('admin/alliances.al_rank_see_memberslist') }}"
                                    title="{{ __('admin/alliances.al_rank_see_memberslist') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_see_memberslist') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r5.png') }}" alt="{{ __('admin/alliances.al_rank_check_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_check_requests') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_check_requests') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r6.png') }}" alt="{{ __('admin/alliances.al_rank_manage_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_manage_alliance') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_manage_alliance') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r7.png') }}"
                                    alt="{{ __('admin/alliances.al_rank_see_online_members') }}" title="{{ __('admin/alliances.al_rank_see_online_members') }}">
                            </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_see_online_members') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r8.png') }}" alt="{{ __('admin/alliances.al_rank_create_circular') }}"
                                    title="{{ __('admin/alliances.al_rank_create_circular') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_create_circular') }}</td>
                        </tr>
                        <tr>
                            <td class="text-center">
                                <img src="{{ asset('assets/upload/skins/xgproyect/img/r9.png') }}" alt="{{ __('admin/alliances.al_rank_right_hand') }}"
                                    title="{{ __('admin/alliances.al_rank_right_hand') }}">
                                </td>
                            <td class="text-center">{{ __('admin/alliances.al_rank_right_hand') }}</td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
