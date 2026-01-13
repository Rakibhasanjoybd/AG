@extends($activeTemplate.'layouts.master_mobile')
@section('main-content')

<!-- Page Header -->
<div class="page-header">
    <h2><i class="fas fa-plus-circle me-2"></i>নতুন টিকেট</h2>
    <a href="{{ route('ticket') }}" class="back-link">
        <i class="fas fa-list"></i>
    </a>
</div>

<!-- Create Ticket Form -->
<div class="section-block">
    <form action="{{ route('ticket.store') }}" method="post" enctype="multipart/form-data" onsubmit="return submitUserForm();">
        @csrf

        <div class="form-group">
            <label class="form-lbl">@lang('Name')</label>
            <input type="text" name="name" value="{{ @$user->firstname . ' ' . @$user->lastname }}" class="form-input readonly" required readonly>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Email')</label>
            <input type="email" name="email" value="{{ @$user->email }}" class="form-input readonly" required readonly>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Subject')</label>
            <input type="text" name="subject" value="{{ old('subject') }}" class="form-input" placeholder="সমস্যার বিষয়" required>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Priority')</label>
            <select name="priority" class="form-input" required>
                <option value="3">উচ্চ (High)</option>
                <option value="2" selected>মাঝারি (Medium)</option>
                <option value="1">নিম্ন (Low)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-lbl">@lang('Message')</label>
            <textarea name="message" rows="5" class="form-input" placeholder="আপনার সমস্যার বিবরণ লিখুন..." required>{{ old('message') }}</textarea>
        </div>

        <div class="form-group">
            <div class="attach-header">
                <label class="form-lbl">@lang('Attachments')</label>
                <button type="button" class="add-file-btn addFile">
                    <i class="fas fa-plus"></i> যোগ করুন
                </button>
            </div>
            <input type="file" name="attachments[]" class="form-input file-input">
            <div id="fileUploadsContainer"></div>
            <p class="file-hint">সর্বোচ্চ ৫টি ফাইল • JPG, PNG, PDF, DOC • সাইজ {{ ini_get('upload_max_filesize') }}</p>
        </div>

        <button type="submit" class="btn-submit" id="recaptcha">
            <i class="fas fa-paper-plane me-2"></i>@lang('Submit Ticket')
        </button>
    </form>
</div>

<div style="height: 20px;"></div>
@endsection

@push('style')
<style>
.page-header{background:var(--purple);padding:20px;display:flex;justify-content:space-between;align-items:center}
.page-header h2{color:var(--white);font-size:18px;font-weight:700;margin:0;display:flex;align-items:center}
.back-link{width:40px;height:40px;background:rgba(255,255,255,0.15);border-radius:12px;color:var(--white);font-size:16px;display:flex;align-items:center;justify-content:center;text-decoration:none}

.section-block{background:var(--white);margin:16px;border-radius:20px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.04)}

.form-group{margin-bottom:16px}
.form-lbl{display:block;font-size:12px;color:var(--gray);margin-bottom:8px;font-weight:500}
.form-input{width:100%;padding:14px;border:1.5px solid #e5e7eb;border-radius:12px;font-size:14px;font-family:inherit;background:var(--white);transition:all 0.2s}
.form-input:focus{outline:none;border-color:var(--purple);box-shadow:0 0 0 3px rgba(82,0,106,0.1)}
.form-input.readonly{background:#f8f9fa;color:var(--gray)}
textarea.form-input{resize:vertical;min-height:100px}
select.form-input{cursor:pointer}

.attach-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px}
.add-file-btn{background:var(--gold);color:var(--purple);border:none;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:600;font-family:inherit;cursor:pointer;display:flex;align-items:center;gap:6px}
.file-input{font-size:13px}
.file-hint{font-size:11px;color:var(--gray);margin-top:8px}

.btn-submit{width:100%;padding:14px;background:var(--purple);color:var(--white);border:none;border-radius:14px;font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;margin-top:8px;display:flex;align-items:center;justify-content:center}

.file-remove-wrap{display:flex;gap:8px;margin-top:8px}
.file-remove-wrap input{flex:1}
.remove-btn{width:42px;background:var(--crimson);border:none;border-radius:10px;color:var(--white);cursor:pointer}
</style>
@endpush

@push('script')
<script>
(function($) {
    "use strict";
    var fileAdded = 0;
    $('.addFile').on('click', function() {
        if (fileAdded >= 4) {
            if(typeof notify === 'function') notify('error', 'সর্বোচ্চ ৫টি ফাইল আপলোড করা যাবে');
            return false;
        }
        fileAdded++;
        $("#fileUploadsContainer").append(`
            <div class="file-remove-wrap">
                <input type="file" name="attachments[]" class="form-input file-input" required />
                <button type="button" class="remove-btn"><i class="fas fa-times"></i></button>
            </div>
        `);
    });
    $(document).on('click', '.remove-btn', function() {
        fileAdded--;
        $(this).closest('.file-remove-wrap').remove();
    });
})(jQuery);
</script>
@endpush
