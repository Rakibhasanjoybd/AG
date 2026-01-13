@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <form action="{{ route('admin.whatsapp.contacts.update', $contact->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $contact->name) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Department <span class="text-danger">*</span></label>
                                <input type="text" name="department" class="form-control" value="{{ old('department', $contact->department) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number (with country code) <span class="text-danger">*</span></label>
                                <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $contact->phone_number) }}" placeholder="e.g., +8801712345678" required>
                                <small class="text-muted">Format: +CountryCodePhoneNumber</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Display Order</label>
                                <input type="number" name="display_order" class="form-control" value="{{ old('display_order', $contact->display_order) }}" min="0">
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $contact->description) }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Message Format</label>
                                <textarea name="message_format" class="form-control" rows="4">{{ old('message_format', $contact->message_format) }}</textarea>
                                <small class="text-muted">Pre-filled message that will appear when user clicks to chat</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Profile Image</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*" id="profileImageInput">
                                <small class="text-muted">Recommended: Square image (e.g., 200x200px)</small>
                            </div>
                            <div class="mt-2">
                                <img id="imagePreview" src="{{ $contact->profile_image_url }}" alt="Profile" style="max-width: 150px; max-height: 150px; border-radius: 10px; border: 2px solid #ddd;">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="d-block">Status</label>
                                <div class="form-check form-switch form-check-primary">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $contact->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.whatsapp.contacts.index') }}" class="btn btn-secondary">
                            <i class="las la-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="las la-save"></i> Update Contact
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function($){
        "use strict";

        // Image preview
        $('#profileImageInput').on('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
    })(jQuery);
</script>
@endpush

