@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body">
                <form action="{{ route('admin.popup.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="form-label">@lang('Title') <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">@lang('Content')</label>
                                <textarea name="content" class="form-control" rows="4">{{ old('content') }}</textarea>
                                <small class="text-muted">@lang('This will be displayed in the popup body')</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Button Text')</label>
                                        <input type="text" name="button_text" class="form-control" value="{{ old('button_text') }}" placeholder="@lang('e.g., Learn More')">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">@lang('Button Link')</label>
                                        <input type="text" name="button_link" class="form-control" value="{{ old('button_link') }}" placeholder="@lang('e.g., /user/plans')">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">@lang('Popup Media (Image or Video)')</label>
                                
                                <div class="mb-3">
                                    <label class="form-label small text-muted" for="mediaFile">@lang('Option 1: Upload File')</label>
                                    <input type="file" name="media" id="mediaFile" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime,video/x-msvideo">
                                    <small class="text-muted">
                                        @lang('Supported: JPG, PNG, GIF, WebP, MP4, WebM, MOV, AVI, MKV') | @lang('Max 10MB')
                                    </small>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small text-muted" for="mediaUrl">@lang('Option 2: External URL')</label>
                                    <input type="url" name="media_url" id="mediaUrl" class="form-control" placeholder="https://example.com/image.jpg or video URL" value="{{ old('media_url') }}">
                                    <small class="text-muted">
                                        @lang('Paste any external image/video/GIF URL. This will override the file upload.')
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title mb-3"><i class="las la-cog"></i> @lang('Popup Settings')</h6>

                                    <div class="form-group">
                                        <label class="form-label">@lang('Target Audience') <span class="text-danger">*</span></label>
                                        <select name="target_type" class="form-control" id="targetType" required>
                                            <option value="all" {{ old('target_type') == 'all' ? 'selected' : '' }}>@lang('All Users')</option>
                                            <option value="specific" {{ old('target_type') == 'specific' ? 'selected' : '' }}>@lang('Specific Users')</option>
                                        </select>
                                    </div>

                                    <div class="form-group" id="userSelectGroup" style="display: none;">
                                        <label class="form-label">@lang('Select Users')</label>
                                        <select name="target_users[]" class="form-control select2-multi" multiple>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->username }} - {{ $user->email }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">@lang('Priority')</label>
                                        <input type="number" name="priority" class="form-control" value="{{ old('priority', 0) }}" min="0" max="100">
                                        <small class="text-muted">@lang('Higher priority shows first (0-100)')</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">@lang('Start Date')</label>
                                                <input type="datetime-local" name="start_date" class="form-control" value="{{ old('start_date') }}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label class="form-label">@lang('End Date')</label>
                                                <input type="datetime-local" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="show_once" value="1" class="form-check-input" id="showOnce" {{ old('show_once', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="showOnce">@lang('Show only once per user')</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="show_to_guests" value="1" class="form-check-input" id="showToGuests" {{ old('show_to_guests') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="showToGuests">@lang('Show to guest users')</label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" name="status" value="1" class="form-check-input" id="status" {{ old('status', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status">@lang('Active')</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn--primary w-100 h-45">
                            <i class="las la-save"></i> @lang('Create Popup')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<a href="{{ route('admin.popup.index') }}" class="btn btn-sm btn-outline--primary">
    <i class="la la-arrow-left"></i> @lang('Back to List')
</a>
@endpush

@push('style-lib')
<link rel="stylesheet" href="{{ asset('assets/admin/css/vendor/select2.min.css') }}">
@endpush

@push('script-lib')
<script src="{{ asset('assets/admin/js/vendor/select2.min.js') }}"></script>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    // Initialize Select2 for multi-select
    $('.select2-multi').select2({
        placeholder: "{{ __('Select users...') }}",
        width: '100%'
    });

    // Toggle user select based on target type
    $('#targetType').on('change', function(){
        if($(this).val() === 'specific'){
            $('#userSelectGroup').slideDown();
        } else {
            $('#userSelectGroup').slideUp();
        }
    }).trigger('change');

})(jQuery);
</script>
@endpush
