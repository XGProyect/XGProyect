<div class="card shadow mb-4">
    <!-- Card Header - Accordion -->
    <a href="#collapsePremium" class="d-block card-header py-3" data-toggle="collapse" role="button"
        aria-expanded="true" aria-controls="collapsePremium">
        <h6 class="m-0 font-weight-bold text-primary">{{ $premium }}</h6>
    </a>
    <!-- Card Content - Collapse -->
    <div class="collapse show" id="collapsePremium" style="">
        <div class="card-body">
            <div class="table-responsive">
                <form name="save_info" method="post" action="">
                    <table class="table table-borderless" width="100%" cellspacing="0">
                        <tr>
                            <td>{{ __('admin/users.us_user_premium_dark_matter') }}</td>
                            <td>
                                <input type="number" class="form-control" name="premium_dark_matter"
                                    value="{{ $premium_dark_matter }}">
                            </td>
                        </tr>
                        @foreach ($premium_list as $item)
                        <tr>
                            <td>
                                {{ $item['premium'] }}
                                <br>
                                <span class="small font-weight-bold {{ $item['status_style'] }}">{{ $item['status'] }}</span>
                            </td>
                            <td>
                                <select name="{{ $item['field'] }}" class="form-control">
                                    {!! $item['combo'] !!}
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                    <div class="text-center">
                        <input type="hidden" name="send_data" value="1">
                        <button type="submit" class="btn btn-primary btn-icon-split">
                            <span class="icon text-white-50">
                                <i class="fas fa-save"></i>
                            </span>
                            <span class="text">{{ __('admin/users.us_send_data') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>