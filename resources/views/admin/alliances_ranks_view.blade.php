<div class="card shadow mb-4">
    <!-- Card Header - Accordion -->
    <a href="#collapseRanks" class="d-block card-header py-3" data-toggle="collapse" role="button" aria-expanded="true"
        aria-controls="collapseRanks">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/alliances.al_alliance_ranks') }}</h6>
    </a>
    <!-- Card Content - Collapse -->
    <div class="collapse show" id="collapseRanks" style="">
        <div class="card-body">
            <div class="table-responsive">
                {alert_info}
                <form name="save_ranks" method="post" action="">
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <th colspan="11">{{ __('admin/alliances.al_configure_ranks') }}</th>
                        </tr>
                        <tr>
                            <td class="align_center">X</td>
                            <th>{{ __('admin/alliances.al_rank_name') }}</th>
                            <td class="align_center"><img src="{image_path}img/r1.png" alt="{{ __('admin/alliances.al_rank_delete_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_delete_alliance') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r2.png" alt="{{ __('admin/alliances.al_rank_kick_members') }}"
                                    title="{{ __('admin/alliances.al_rank_kick_members') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r3.png" alt="{{ __('admin/alliances.al_rank_see_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_see_requests') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r4.png" alt="{{ __('admin/alliances.al_rank_see_memberslist') }}"
                                    title="{{ __('admin/alliances.al_rank_see_memberslist') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r5.png" alt="{{ __('admin/alliances.al_rank_check_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_check_requests') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r6.png" alt="{{ __('admin/alliances.al_rank_manage_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_manage_alliance') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r7.png"
                                    alt="{{ __('admin/alliances.al_rank_see_online_members') }}" title="{{ __('admin/alliances.al_rank_see_online_members') }}">
                            </td>
                            <td class="align_center"><img src="{image_path}img/r8.png" alt="{{ __('admin/alliances.al_rank_create_circular') }}"
                                    title="{{ __('admin/alliances.al_rank_create_circular') }}"></td>
                            <td class="align_center"><img src="{image_path}img/r9.png" alt="{{ __('admin/alliances.al_rank_right_hand') }}"
                                    title="{{ __('admin/alliances.al_rank_right_hand') }}"></td>
                        </tr>
                        {ranks_table}
                        <tr>
                            <td colspan="11">
                                <div align="center">
                                    <input type="submit" name="save_ranks" value="{{ __('admin/alliances.al_save_ranks') }}"
                                        class="btn btn-primary">
                                    <input type="submit" name="delete_ranks" value="{{ __('admin/alliances.al_delete_ranks') }}"
                                        class="btn btn-primary">
                                </div>
                            </td>
                        </tr>
                    </table>
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <th colspan="2">{{ __('admin/alliances.al_create_ranks') }}</th>
                        </tr>
                        <tr>
                            <td class="align_center">{{ __('admin/alliances.al_rank_name') }}</td>
                            <td class="align_center">
                                <input type="text" name="rank_name">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="align_center">
                                <input type="submit" name="create_rank" value="{{ __('admin/alliances.al_create_ranks') }}"
                                    class="btn btn-primary">
                            </td>
                        </tr>
                    </table>
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <th colspan="2">{{ __('admin/alliances.al_ranks_leyend') }}</th>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r1.png" alt="{{ __('admin/alliances.al_rank_delete_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_delete_alliance') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_delete_alliance') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r2.png" alt="{{ __('admin/alliances.al_rank_kick_members') }}"
                                    title="{{ __('admin/alliances.al_rank_kick_members') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_kick_members') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r3.png" alt="{{ __('admin/alliances.al_rank_see_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_see_requests') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_see_requests') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r4.png" alt="{{ __('admin/alliances.al_rank_see_memberslist') }}"
                                    title="{{ __('admin/alliances.al_rank_see_memberslist') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_see_memberslist') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r5.png" alt="{{ __('admin/alliances.al_rank_check_requests') }}"
                                    title="{{ __('admin/alliances.al_rank_check_requests') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_check_requests') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r6.png" alt="{{ __('admin/alliances.al_rank_manage_alliance') }}"
                                    title="{{ __('admin/alliances.al_rank_manage_alliance') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_manage_alliance') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r7.png"
                                    alt="{{ __('admin/alliances.al_rank_see_online_members') }}" title="{{ __('admin/alliances.al_rank_see_online_members') }}">
                            </td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_see_online_members') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r8.png" alt="{{ __('admin/alliances.al_rank_create_circular') }}"
                                    title="{{ __('admin/alliances.al_rank_create_circular') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_create_circular') }}</td>
                        </tr>
                        <tr>
                            <td class="align_center"><img src="{image_path}img/r9.png" alt="{{ __('admin/alliances.al_rank_right_hand') }}"
                                    title="{{ __('admin/alliances.al_rank_right_hand') }}"></td>
                            <td class="align_center">{{ __('admin/alliances.al_rank_right_hand') }}</td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
