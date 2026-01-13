@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Android APK Section -->
    <div class="col-lg-6">
        <div class="card b-radius--10">
            <div class="card-header bg--primary">
                <h5 class="card-title text-white mb-0">
                    <i class="lab la-android me-2"></i>@lang('Android APK')
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.app.download.update', 'android') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>@lang('App Version') <span class="text-danger">*</span></label>
                                <input type="text" name="version" class="form-control" value="{{ @$android->version }}" placeholder="e.g. 1.0.0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>@lang('APK File') @if(!@$android->file_path)<span class="text-danger">*</span>@endif</label>
                                <input type="file" name="file" class="form-control" accept=".apk" {{ !@$android->file_path ? 'required' : '' }}>
                                <small class="text-muted">@lang('Max size: 100MB. Only .apk files allowed.')</small>
                                @if(@$android->file_path)
                                    <div class="mt-2">
                                        <span class="badge badge--success">
                                            <i class="las la-check-circle"></i> @lang('Current file'): {{ @$android->file_name }} ({{ @$android->file_size_formatted }})
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>@lang('Description')</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="@lang('What\'s new in this version...')">{{ @$android->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-check-label">
                                    <input type="checkbox" name="force_update" value="1" {{ @$android->force_update ? 'checked' : '' }}>
                                    @lang('Force Update') <small class="text-muted">(@lang('Users must update to continue'))</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            @if(@$android)
                                <span class="badge badge--{{ @$android->status ? 'success' : 'danger' }}">
                                    {{ @$android->status ? __('Active') : __('Inactive') }}
                                </span>
                                <span class="badge badge--dark ms-2">
                                    <i class="las la-download"></i> {{ number_format(@$android->download_count) }} @lang('downloads')
                                </span>
                            @endif
                        </div>
                        <div>
                            @if(@$android)
                                <button type="button" class="btn btn--{{ @$android->status ? 'warning' : 'success' }} btn-sm me-1" onclick="toggleStatus('android')">
                                    <i class="las la-{{ @$android->status ? 'ban' : 'check' }}"></i> {{ @$android->status ? __('Disable') : __('Enable') }}
                                </button>
                            @endif
                            <button type="submit" class="btn btn--primary">
                                <i class="las la-save"></i> @lang('Save')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- iOS Profile Section -->
    <div class="col-lg-6">
        <div class="card b-radius--10">
            <div class="card-header bg--dark">
                <h5 class="card-title text-white mb-0">
                    <i class="lab la-apple me-2"></i>@lang('iOS Profile')
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.app.download.update', 'ios') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>@lang('App Version') <span class="text-danger">*</span></label>
                                <input type="text" name="version" class="form-control" value="{{ @$ios->version }}" placeholder="e.g. 1.0.0" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>@lang('iOS Profile File') @if(!@$ios->file_path)<span class="text-danger">*</span>@endif</label>
                                <input type="file" name="file" class="form-control" accept=".mobileconfig,.plist" {{ !@$ios->file_path ? 'required' : '' }}>
                                <small class="text-muted">@lang('Max size: 10MB. Only .mobileconfig or .plist files allowed.')</small>
                                @if(@$ios->file_path)
                                    <div class="mt-2">
                                        <span class="badge badge--success">
                                            <i class="las la-check-circle"></i> @lang('Current file'): {{ @$ios->file_name }} ({{ @$ios->file_size_formatted }})
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>@lang('Description')</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="@lang('What\'s new in this version...')">{{ @$ios->description }}</textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-check-label">
                                    <input type="checkbox" name="force_update" value="1" {{ @$ios->force_update ? 'checked' : '' }}>
                                    @lang('Force Update') <small class="text-muted">(@lang('Users must update to continue'))</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            @if(@$ios)
                                <span class="badge badge--{{ @$ios->status ? 'success' : 'danger' }}">
                                    {{ @$ios->status ? __('Active') : __('Inactive') }}
                                </span>
                                <span class="badge badge--dark ms-2">
                                    <i class="las la-download"></i> {{ number_format(@$ios->download_count) }} @lang('downloads')
                                </span>
                            @endif
                        </div>
                        <div>
                            @if(@$ios)
                                <button type="button" class="btn btn--{{ @$ios->status ? 'warning' : 'success' }} btn-sm me-1" onclick="toggleStatus('ios')">
                                    <i class="las la-{{ @$ios->status ? 'ban' : 'check' }}"></i> {{ @$ios->status ? __('Disable') : __('Enable') }}
                                </button>
                            @endif
                            <button type="submit" class="btn btn--primary">
                                <i class="las la-save"></i> @lang('Save')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Instructions Card -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="las la-info-circle me-2"></i>@lang('Installation Instructions')</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary"><i class="lab la-android me-1"></i> @lang('Android Users')</h6>
                        <ul class="list-unstyled">
                            <li><i class="las la-check text-success me-1"></i> @lang('Upload APK file here')</li>
                            <li><i class="las la-check text-success me-1"></i> @lang('Users click Install button')</li>
                            <li><i class="las la-check text-success me-1"></i> @lang('APK downloads automatically')</li>
                            <li><i class="las la-check text-success me-1"></i> @lang('User opens APK to install')</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-dark"><i class="lab la-apple me-1"></i> @lang('iOS Users')</h6>
                        <ul class="list-unstyled">
                            <li><i class="las la-check text-success me-1"></i> @lang('Upload .mobileconfig profile')</li>
                            <li><i class="las la-check text-success me-1"></i> @lang('Users click Install button')</li>
                            <li><i class="las la-check text-success me-1"></i> @lang('Profile downloads automatically')</li>
                            <li><i class="las la-check text-success me-1"></i> @lang('User installs from Settings')</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden forms for status toggle -->
<form id="statusFormAndroid" action="{{ route('admin.app.download.status', 'android') }}" method="POST" class="d-none">@csrf</form>
<form id="statusFormIos" action="{{ route('admin.app.download.status', 'ios') }}" method="POST" class="d-none">@csrf</form>
@endsection

@push('script')
<script>
    function toggleStatus(platform) {
        if (platform === 'android') {
            document.getElementById('statusFormAndroid').submit();
        } else {
            document.getElementById('statusFormIos').submit();
        }
    }
</script>
@endpush
