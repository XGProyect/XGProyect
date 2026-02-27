@extends('master.admin')

@section('content')
<div class="container-fluid">
    <script type="text/javascript" src="{{ asset('assets/js/filterlist-min.js') }}"></script>
    <x-alert/>
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('admin/ban.bn_title') }}</h1>
    </div>
    <p class="mb-4">{{ __('admin/ban.bn_sub_title') }}</p>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <!-- Card Header - Accordion -->
                <a href="#collapseGeneral" class="d-block card-header py-3" data-toggle="collapse" role="button"
                    aria-expanded="true" aria-controls="collapseGeneral">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('admin/ban.bn_general') }}</h6>
                </a>
                <!-- Card Content - Collapse -->
                <div class="collapse show" id="collapseGeneral" style="">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless" width="100%" cellspacing="0">
                                <tbody>
                                    <tr>
                                        <td>
                                            <form action="" method="GET" name="users">
                                                <input type="hidden" name="page" value="ban">
                                                <input type="hidden" name="mode" value="ban">
                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-primary btn-icon-split"
                                                        name="banuser"
                                                        value="{{ __('admin/ban.bn_button_ban') }}">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-user-slash"></i>
                                                        </span>
                                                        <span class="text">{{ __('admin/ban.bn_button_ban') }}</span>
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-icon-split"
                                                        onClick="UserList.reset();this.form.regexp.value = ''"
                                                        value="{{ __('admin/ban.bn_button_reset') }}">
                                                        <span class="text">{{ __('admin/ban.bn_button_reset') }}</span>
                                                    </button>
                                                </div>
                                                <table width="100%">
                                                    <tr>
                                                        <td style="border:0px;">{{ __('admin/ban.bn_users_list') }}
                                                            ({{ __('admin/ban.bn_total_users') }}{{ $users_amount }})
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:0px;">
                                                            <div class="text-center">
                                                                <select name="ban_name" class="form-control" style="width:100%;" size="20">
                                                                    {!! $users_list !!}
                                                                </select>
                                                                <script type="text/javascript">
                                                                    var UserList = new filterlist(document.users.ban_name);
                                                                </script>
                                                                <br>
                                                                <span class="small">
                                                                    <a href="javascript:UserList.set('^A')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} A">A</a>
                                                                    <a href="javascript:UserList.set('^B')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} B">B</a>
                                                                    <a href="javascript:UserList.set('^C')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} C">C</a>
                                                                    <a href="javascript:UserList.set('^D')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} D">D</a>
                                                                    <a href="javascript:UserList.set('^E')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} E">E</a>
                                                                    <a href="javascript:UserList.set('^F')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} F">F</a>
                                                                    <a href="javascript:UserList.set('^G')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} G">G</a>
                                                                    <a href="javascript:UserList.set('^H')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} H">H</a>
                                                                    <a href="javascript:UserList.set('^I')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} I">I</a>
                                                                    <a href="javascript:UserList.set('^J')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} J">J</a>
                                                                    <a href="javascript:UserList.set('^K')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} K">K</a>
                                                                    <a href="javascript:UserList.set('^L')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} L">L</a>
                                                                    <a href="javascript:UserList.set('^M')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} M">M</a>
                                                                    <a href="javascript:UserList.set('^N')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} N">N</a>
                                                                    <a href="javascript:UserList.set('^O')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} O">O</a>
                                                                    <a href="javascript:UserList.set('^P')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} P">P</a>
                                                                    <a href="javascript:UserList.set('^Q')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} Q">Q</a>
                                                                    <a href="javascript:UserList.set('^R')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} R">R</a>
                                                                    <a href="javascript:UserList.set('^S')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} S">S</a>
                                                                    <a href="javascript:UserList.set('^T')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} T">T</a>
                                                                    <a href="javascript:UserList.set('^U')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} U">U</a>
                                                                    <a href="javascript:UserList.set('^V')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} V">V</a>
                                                                    <a href="javascript:UserList.set('^W')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} W">W</a>
                                                                    <a href="javascript:UserList.set('^X')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} X">X</a>
                                                                    <a href="javascript:UserList.set('^Y')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} Y">Y</a>
                                                                    <a href="javascript:UserList.set('^Z')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} Z">Z</a>
                                                                </span>
                                                                <br>
                                                                <span class="small">
                                                                    {{ __('admin/ban.bn_sort') }}:
                                                                    <a href="/admin/ban">{{ __('admin/ban.bn_sort_by_user') }}</a>
                                                                    <a href="/admin/ban?order=id">{{ __('admin/ban.bn_sort_by_id') }}</a>
                                                                    <a href="/admin/ban?view=user_banned">{{ __('admin/ban.bn_sort_suspended') }}</a>
                                                                </span>
                                                                <br><br>
                                                                <br>
                                                                <input type="text" class="form-control" name="regexp" onKeyUp="UserList.set(this.value)">
                                                                <br>
                                                                <div class="text-center">
                                                                    <button type="button" class="btn btn-primary btn-icon-split"
                                                                        onClick="UserList.set(this.form.regexp.value)"
                                                                        value="{{ __('admin/ban.bn_button_filter') }}">
                                                                        <span class="icon text-white-50">
                                                                            <i class="fas fa-filter"></i>
                                                                        </span>
                                                                        <span class="text">{{ __('admin/ban.bn_button_filter') }}</span>
                                                                    </button>
                                                                    <button type="button" class="btn btn-primary btn-icon-split"
                                                                        onClick="UserList.reset();this.form.regexp.value = ''"
                                                                        value="{{ __('admin/ban.bn_button_remove') }}">
                                                                        <span class="text">{{ __('admin/ban.bn_button_remove') }}</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border: 0px;">
                                                            <div class="text-center">
                                                                <button type="submit" class="btn btn-primary btn-icon-split"
                                                                    name="banuser"
                                                                    value="{{ __('admin/ban.bn_button_ban') }}">
                                                                    <span class="icon text-white-50">
                                                                        <i class="fas fa-user-slash"></i>
                                                                    </span>
                                                                    <span class="text">{{ __('admin/ban.bn_button_ban') }}</span>
                                                                </button>
                                                                <button type="button" class="btn btn-primary btn-icon-split"
                                                                    onClick="UserList.reset();this.form.regexp.value = ''"
                                                                    value="{{ __('admin/ban.bn_button_reset') }}">
                                                                    <span class="text">{{ __('admin/ban.bn_button_reset') }}</span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="" method="POST" name="userban">
                                                <div class="text-center">
                                                    <button type="submit" class="btn btn-primary btn-icon-split"
                                                        name="liftbanuser"
                                                        value="{{ __('admin/ban.bn_button_lift_ban') }}">
                                                        <span class="icon text-white-50">
                                                            <i class="fas fa-user"></i>
                                                        </span>
                                                        <span class="text">{{ __('admin/ban.bn_button_lift_ban') }}</span>
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-icon-split"
                                                        onClick="UsersBan.reset();this.form.regexp.value = ''"
                                                        value="{{ __('admin/ban.bn_button_reset') }}">
                                                        <span class="text">{{ __('admin/ban.bn_button_reset') }}</span>
                                                    </button>
                                                </div>
                                                <table width="100%">
                                                    <tr>
                                                        <td style="border:0px;">{{ __('admin/ban.bn_banned_list') }}
                                                            ({{ __('admin/ban.bn_total_users') }}{{ $banned_amount }})
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:0px;">
                                                            <div class="text-center">
                                                                <select name="unban_name" class="form-control" style="width:100%;" size="20">
                                                                    {!! $banned_list !!}
                                                                </select>
                                                                <script type="text/javascript">
                                                                    var UsersBan = new filterlist(document.userban.unban_name);
                                                                </script>
                                                                <br>
                                                                <span class="small">
                                                                    <a href="javascript:UsersBan.set('^A')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} A">A</a>
                                                                    <a href="javascript:UsersBan.set('^B')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} B">B</a>
                                                                    <a href="javascript:UsersBan.set('^C')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} C">C</a>
                                                                    <a href="javascript:UsersBan.set('^D')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} D">D</a>
                                                                    <a href="javascript:UsersBan.set('^E')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} E">E</a>
                                                                    <a href="javascript:UsersBan.set('^F')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} F">F</a>
                                                                    <a href="javascript:UsersBan.set('^G')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} G">G</a>
                                                                    <a href="javascript:UsersBan.set('^H')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} H">H</a>
                                                                    <a href="javascript:UsersBan.set('^I')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} I">I</a>
                                                                    <a href="javascript:UsersBan.set('^J')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} J">J</a>
                                                                    <a href="javascript:UsersBan.set('^K')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} K">K</a>
                                                                    <a href="javascript:UsersBan.set('^L')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} L">L</a>
                                                                    <a href="javascript:UsersBan.set('^M')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} M">M</a>
                                                                    <a href="javascript:UsersBan.set('^N')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} N">N</a>
                                                                    <a href="javascript:UsersBan.set('^O')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} O">O</a>
                                                                    <a href="javascript:UsersBan.set('^P')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} P">P</a>
                                                                    <a href="javascript:UsersBan.set('^Q')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} Q">Q</a>
                                                                    <a href="javascript:UsersBan.set('^R')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} R">R</a>
                                                                    <a href="javascript:UsersBan.set('^S')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} S">S</a>
                                                                    <a href="javascript:UsersBan.set('^T')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} T">T</a>
                                                                    <a href="javascript:UsersBan.set('^U')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} U">U</a>
                                                                    <a href="javascript:UsersBan.set('^V')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} V">V</a>
                                                                    <a href="javascript:UsersBan.set('^W')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} W">W</a>
                                                                    <a href="javascript:UsersBan.set('^X')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} X">X</a>
                                                                    <a href="javascript:UsersBan.set('^Y')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} Y">Y</a>
                                                                    <a href="javascript:UsersBan.set('^Z')"
                                                                        title="{{ __('admin/ban.bn_select_title') }} Z">Z</a>
                                                                </span>
                                                                <br>
                                                                <span class="small">
                                                                    {{ __('admin/ban.bn_sort') }}:
                                                                    <a href="/admin/ban">{{ __('admin/ban.bn_sort_by_user') }}</a>
                                                                    <a
                                                                        href="/admin/ban?order2=id">{{ __('admin/ban.bn_sort_by_id') }}</a>
                                                                </span>
                                                                <br><br>
                                                                <br>
                                                                <input type="text" class="form-control" name="regexp" onKeyUp="UsersBan.set(this.value)">
                                                                <br>
                                                                <div class="text-center">
                                                                    <button type="button" class="btn btn-primary btn-icon-split"
                                                                        onClick="UsersBan.set(this.form.regexp.value)"
                                                                        value="{{ __('admin/ban.bn_button_filter') }}">
                                                                        <span class="icon text-white-50">
                                                                            <i class="fas fa-filter"></i>
                                                                        </span>
                                                                        <span class="text">{{ __('admin/ban.bn_button_filter') }}</span>
                                                                    </button>
                                                                    <button type="button" class="btn btn-primary btn-icon-split"
                                                                        onClick="UsersBan.set(this.form.regexp.value)"
                                                                        value="{{ __('admin/ban.bn_button_remove') }}">
                                                                        <span class="text">{{ __('admin/ban.bn_button_remove') }}</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="border:0px;">
                                                            <div class="text-center">
                                                                <button type="submit" class="btn btn-primary btn-icon-split"
                                                                    name="liftbanuser"
                                                                    value="{{ __('admin/ban.bn_button_lift_ban') }}">
                                                                    <span class="icon text-white-50">
                                                                        <i class="fas fa-user"></i>
                                                                    </span>
                                                                    <span class="text">{{ __('admin/ban.bn_button_lift_ban') }}</span>
                                                                </button>
                                                                <button type="button" class="btn btn-primary btn-icon-split"
                                                                    onClick="UsersBan.reset();this.form.regexp.value = ''"
                                                                    value="{{ __('admin/ban.bn_button_reset') }}">
                                                                    <span class="text">{{ __('admin/ban.bn_button_reset') }}</span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection