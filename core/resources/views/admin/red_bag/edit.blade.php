@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.red-bag.update', $redBag->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Name')</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $redBag->name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Minimum Amount') ({{ __($general->cur_text) }})</label>
                                <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ old('min_amount', $redBag->min_amount) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Maximum Amount') ({{ __($general->cur_text) }})</label>
                                <input type="number" step="0.01" name="max_amount" class="form-control" value="{{ old('max_amount', $redBag->max_amount) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Daily Claim Limit')</label>
                                <input type="number" name="daily_limit" class="form-control" value="{{ old('daily_limit', $redBag->daily_limit) }}" required>
                                <small class="text-muted">@lang('How many times per day a user can claim')</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('New User Bonus Count')</label>
                                <input type="number" name="new_user_bonus_count" class="form-control" value="{{ old('new_user_bonus_count', $redBag->new_user_bonus_count) }}" required>
                                <small class="text-muted">@lang('Extra red bags for new users')</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('New User Days')</label>
                                <input type="number" name="new_user_days" class="form-control" value="{{ old('new_user_days', $redBag->new_user_days) }}" required>
                                <small class="text-muted">@lang('Days considered as new user')</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Win Probability') (%)</label>
                                <input type="number" step="0.01" name="win_probability" class="form-control" value="{{ old('win_probability', $redBag->win_probability) }}" min="0" max="100" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Start Time')</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time', \Carbon\Carbon::parse($redBag->start_time)->format('H:i')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('End Time')</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time', \Carbon\Carbon::parse($redBag->end_time)->format('H:i')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Total Daily Budget') ({{ __($general->cur_text) }})</label>
                                <input type="number" step="0.01" name="total_daily_budget" class="form-control" value="{{ old('total_daily_budget', $redBag->total_daily_budget) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Status')</label>
                                <select name="status" class="form-control">
                                    <option value="1" {{ $redBag->status ? 'selected' : '' }}>@lang('Active')</option>
                                    <option value="0" {{ !$redBag->status ? 'selected' : '' }}>@lang('Inactive')</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Require Referrals')</label>
                                <select name="require_referral" class="form-control" id="requireReferral">
                                    <option value="0" {{ !$redBag->require_referral ? 'selected' : '' }}>@lang('No')</option>
                                    <option value="1" {{ $redBag->require_referral ? 'selected' : '' }}>@lang('Yes')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" id="minReferralsGroup" style="{{ $redBag->require_referral ? '' : 'display: none;' }}">
                            <div class="form-group">
                                <label>@lang('Minimum Referrals Required')</label>
                                <input type="number" name="min_referrals" class="form-control" value="{{ old('min_referrals', $redBag->min_referrals) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Winning Message')</label>
                                <textarea name="winning_message" class="form-control" rows="3">{{ old('winning_message', $redBag->winning_message) }}</textarea>
                                <small class="text-muted">@lang('Use {amount} as placeholder for the won amount')</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Losing Message')</label>
                                <textarea name="losing_message" class="form-control" rows="3">{{ old('losing_message', $redBag->losing_message) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>@lang('Today\'s Stats'):</strong><br>
                                @lang('Spent'): {{ showAmount($redBag->spent_today) }} {{ __($general->cur_text) }} / {{ showAmount($redBag->total_daily_budget) }} {{ __($general->cur_text) }}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Update Red Bag')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.red-bag.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-arrow-left"></i>@lang('Back')
    </a>
@endpush

@push('script')
<script>
    (function($){
        "use strict";

        $('#requireReferral').on('change', function() {
            if ($(this).val() == '1') {
                $('#minReferralsGroup').show();
            } else {
                $('#minReferralsGroup').hide();
            }
        });

    })(jQuery);
</script>
@endpush
