<div class="card shadow mb-4">
    <!-- Card Header - Accordion -->
    <a href="#collapsePlanets" class="d-block card-header py-3" data-toggle="collapse" role="button"
        aria-expanded="true" aria-controls="collapsePlanets">
        <h6 class="m-0 font-weight-bold text-primary">{{ $planets }}</h6>
    </a>
    <!-- Card Content - Collapse -->
    <div class="collapse show" id="collapsePlanets" style="">
        <div class="card-body">
            <table class="table table-borderless" width="100%" cellspacing="0">
                @foreach ($planets_list as $item)
                <tr>
                    <td class="text-left">
                        <div class="btn-group">
                            <img src="{{ asset('assets/upload/skins/xgproyect/planets/small/s_' .  $item['planet_image'] . '.jpg') }}" alt="{{ $item['planet_image'] }}.jpg"
                                title="{{ $item['planet_image'] }}.jpg" border="0" {{ $item['planet_image_style'] }}>
                            @if ($item['moon_image'])
                                <img src="{{ asset('assets/upload/skins/xgproyect/planets/small/s_' . $item['moon_image'] . '.jpg') }}" alt="{{ $item['moon_image'] }}.jpg" title="{{ $item['moon_image'] }}.jpg" border="0" {{ $item['moon_image_style'] }}>
                            @endif
                            <button class="btn btn-info dropdown-toggle" data-toggle="dropdown">{{ $item['planet_name'] }}
                                {{ $item['planet_status'] }}
                                [{{ $item['moon_name'] }}{!! $item['moon_status'] !!}]
                                {{ __('admin/users.us_user_planets_actions') }} <span class="caret"></span></button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/users?type=planets&edit=planet&user={{ $item['user'] }}&planet={{ $item['planet_id'] }}">
                                        {{ __('admin/users.us_user_planets_edit') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/users?type=planets&edit=buildings&user={{ $item['user'] }}&planet={{ $item['planet_id'] }}">
                                        {{ __('admin/users.us_user_buildings_edit') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/users?type=planets&edit=ships&user={{ $item['user'] }}&planet={{ $item['planet_id'] }}">
                                        {{ __('admin/users.us_user_ships_edit') }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/users?type=planets&edit=defenses&user={{ $item['user'] }}&planet={{ $item['planet_id'] }}">
                                        {{ __('admin/users.us_user_defenses_edit') }}
                                    </a>
                                </li>
                                <li>
                                    <hr>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/users?type=planets&edit=delete&dltmode=soft&user={{ $item['user'] }}&planet={{ $item['planet_id'] }}">
                                        {{ __('admin/users.us_user_delete_planet') }}
                                        {{ __('admin/users.us_user_delete_pm_soft') }}
                                    </a>
                                </li>
                                <!--<li><a href="/admin/users?type=planets&edit=delete&dltmode=physical&user={{ $item['user'] }}&planet={{ $item['planet_id'] }}">{{ __('admin/users.us_user_delete_planet') }} {{ __('admin/users.us_user_delete_pm_physical') }}</a></li>-->
                                <li>
                                    <hr>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/maker?mode=moon&planet={{ $item['planet_id'] }}">{{ __('admin/users.us_user_add_moon') }}</a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="/admin/users?type=moons&edit=delete&dltmode=soft&user={{ $item['user'] }}&moon={{ $item['moon_id'] }}">{{ __('admin/users.us_user_delete_moon') }}
                                        {{ __('admin/users.us_user_delete_pm_soft') }}
                                    </a>
                                </li>
                                <!--<li><a href="/admin/users?type=moons&edit=delete&dltmode=physical&user={{ $item['user'] }}&moon={{ $item['moon_id'] }}">{{ __('admin/users.us_user_delete_moon') }} {{ __('admin/users.us_user_delete_pm_physical') }}</a></li>-->
                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
</div>
