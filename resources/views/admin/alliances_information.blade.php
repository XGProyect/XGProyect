<div class="card shadow mb-4">
    <!-- Card Header - Accordion -->
    <a href="#collapseInformation" class="d-block card-header py-3" data-toggle="collapse" role="button"
        aria-expanded="true" aria-controls="collapseInformation">
        <h6 class="m-0 font-weight-bold text-primary">{{ $al_alliance_information }}</h6>
    </a>
    <!-- Card Content - Collapse -->
    <div class="collapse show" id="collapseInformation" style="">
        <div class="card-body">
            <div class="table-responsive">
                <form name="save_info" method="post" action="">
                    @csrf
                    <input type="hidden" name="alliance_name_orig" value="{{ $alliance_name }}">
                    <input type="hidden" name="alliance_tag_orig" value="{{ $alliance_tag }}">
                    <input type="hidden" name="alliance_owner_orig" value="{{ $alliance_owner }}">
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_register_time') }}</td>
                            <td>{{ $alliance_register_time }}</td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_name') }}</td>
                            <td><input type="text" class="form-control" name="alliance_name" value="{{ $alliance_name }}"></td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_tag') }}</td>
                            <td><input type="text" class="form-control" name="alliance_tag" value="{{ $alliance_tag }}"></td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_owner') }}</td>
                            <td>
                                <select name="alliance_owner" class="form-control">
                                    {!! $alliance_owner_picker !!}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_web') }}</td>
                            <td><input type="text" class="form-control" name="alliance_web" value="{{ $alliance_web }}"></td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_image') }}</td>
                            <td><input type="text" class="form-control" name="alliance_image" value="{{ $alliance_image }}"></td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_description') }}</td>
                            <td>
                                <textarea class="form-control" name="alliance_description"
                                    rows="10">{{ $alliance_description }}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_text') }}</td>
                            <td>
                                <textarea class="form-control" name="alliance_text" rows="10">{{ $alliance_text }}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_request') }}</td>
                            <td>
                                <textarea class="form-control" name="alliance_request"
                                    rows="10">{{ $alliance_request }}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>{{ __('admin/alliances.al_alliance_information_request_notallow') }}</td>
                            <td>
                                <select name="alliance_request_notallow" class="form-control">
                                    <option value="1" {{ $sel1 }}>{{ __('admin/alliances.al_allow_yes') }}</option>
                                    <option value="0" {{ $sel0 }}>{{ __('admin/alliances.al_allow_no') }}</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <div class="text-center">
                        <input type="hidden" name="send_data" value="1">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-save"></i>
                            </span>
                            <span class="text">{{ __('admin/alliances.al_send_data') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
