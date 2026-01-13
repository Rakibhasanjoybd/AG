@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-4 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--primary b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-user"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">{{ $stats['total_free_users'] }}</span>
                </div>
                <div class="desciption">
                    <span class="text--small">@lang('Total Free Users')</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--success b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-crown"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">{{ $stats['total_premium_users'] }}</span>
                </div>
                <div class="desciption">
                    <span class="text--small">@lang('Total Premium Users')</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--warning b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-users"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">{{ $stats['free_users_with_referrals'] }}</span>
                </div>
                <div class="desciption">
                    <span class="text--small">@lang('Free Users with Referrals')</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Settings Card -->
    <div class="col-lg-6 mb-30">
        <div class="card b-radius--10">
            <div class="card-header bg--primary">
                <h5 class="card-title text-white">
                    <i class="las la-cog"></i> @lang('Free User System Settings')
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.free-user.update') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="fw-bold">@lang('Free User System')</label>
                        <select name="free_user_system_enabled" class="form-control">
                            <option value="1" {{ $general->free_user_system_enabled == 1 ? 'selected' : '' }}>@lang('Enabled')</option>
                            <option value="0" {{ $general->free_user_system_enabled == 0 ? 'selected' : '' }}>@lang('Disabled')</option>
                        </select>
                        <small class="text-muted">@lang('Enable or disable the entire free user system')</small>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold">@lang('Free Users Can Earn Referral Commission')</label>
                        <select name="free_user_can_earn_referral" class="form-control">
                            <option value="1" {{ $general->free_user_can_earn_referral == 1 ? 'selected' : '' }}>@lang('Yes')</option>
                            <option value="0" {{ $general->free_user_can_earn_referral == 0 ? 'selected' : '' }}>@lang('No')</option>
                        </select>
                        <small class="text-muted">@lang('Allow free users to earn referral commissions')</small>
                    </div>

                    <div class="form-group">
                        <label class="fw-bold">@lang('Max Referral Levels for Free Users')</label>
                        <input type="number" name="free_user_referral_level" class="form-control" value="{{ $general->free_user_referral_level ?? 1 }}" min="0" max="10">
                        <small class="text-muted">@lang('Maximum referral levels free users can earn from (0 = no commission)')</small>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3"><i class="las la-layer-group"></i> @lang('Step Commission System')</h6>
                    <p class="text-muted small mb-3">@lang('Progressive commission based on referral count: 1st=৳X, 2nd=৳Y, 3rd=৳Z...')</p>

                    <div class="form-group">
                        <label class="fw-bold">@lang('Enable Step Commission')</label>
                        <select name="free_user_step_commission_enabled" class="form-control">
                            <option value="1" {{ ($general->free_user_step_commission_enabled ?? 0) == 1 ? 'selected' : '' }}>@lang('Enabled')</option>
                            <option value="0" {{ ($general->free_user_step_commission_enabled ?? 0) == 0 ? 'selected' : '' }}>@lang('Disabled')</option>
                        </select>
                        <small class="text-muted">@lang('When enabled, free users get progressive commission per referral number')</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Base Amount (1st Referral)')</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="any" name="free_user_step_base_amount" class="form-control" value="{{ getAmount($general->free_user_step_base_amount ?? 100) }}">
                                </div>
                                <small class="text-muted">@lang('Commission for 1st referral')</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Step Increment')</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="any" name="free_user_step_increment" class="form-control" value="{{ getAmount($general->free_user_step_increment ?? 100) }}">
                                </div>
                                <small class="text-muted">@lang('Amount added per step (2nd=Base+Inc, 3rd=Base+2*Inc...)')</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Max Steps')</label>
                                <input type="number" name="free_user_step_max" class="form-control" value="{{ $general->free_user_step_max ?? 10 }}" min="1" max="100">
                                <small class="text-muted">@lang('Maximum number of steps (after this, last amount repeats)')</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Preview')</label>
                                <div class="step-preview bg-light p-2 rounded small">
                                    <span class="badge bg-success">1st: ৳{{ getAmount($general->free_user_step_base_amount ?? 100) }}</span>
                                    <span class="badge bg-info">2nd: ৳{{ getAmount(($general->free_user_step_base_amount ?? 100) + ($general->free_user_step_increment ?? 100)) }}</span>
                                    <span class="badge bg-primary">3rd: ৳{{ getAmount(($general->free_user_step_base_amount ?? 100) + 2*($general->free_user_step_increment ?? 100)) }}</span>
                                    <span class="badge bg-secondary">...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3"><i class="las la-money-bill"></i> @lang('Withdrawal Settings')</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Daily Limit')</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="any" name="free_user_daily_withdraw_limit" class="form-control" value="{{ getAmount($general->free_user_daily_withdraw_limit ?? 100) }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Min Withdraw')</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="any" name="free_user_min_withdraw" class="form-control" value="{{ getAmount($general->free_user_min_withdraw ?? 50) }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Max Withdraw')</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="any" name="free_user_max_withdraw" class="form-control" value="{{ getAmount($general->free_user_max_withdraw ?? 500) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3"><i class="las la-ad"></i> @lang('PTC Settings')</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Can View PTC')</label>
                                <select name="free_user_can_view_ptc" class="form-control">
                                    <option value="1" {{ ($general->free_user_can_view_ptc ?? 1) == 1 ? 'selected' : '' }}>@lang('Yes')</option>
                                    <option value="0" {{ ($general->free_user_can_view_ptc ?? 1) == 0 ? 'selected' : '' }}>@lang('No')</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">@lang('Daily PTC Limit')</label>
                                <input type="number" name="free_user_ptc_limit" class="form-control" value="{{ $general->free_user_ptc_limit ?? 5 }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="fw-bold">@lang('PTC Earning/View')</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" step="any" name="free_user_ptc_earning" class="form-control" value="{{ getAmount($general->free_user_ptc_earning ?? 0.5) }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="text-muted mb-3"><i class="las la-gift"></i> @lang('Red Bag Settings')</h6>

                    <div class="form-group">
                        <label class="fw-bold">@lang('Can Claim Red Bag')</label>
                        <select name="free_user_can_claim_red_bag" class="form-control">
                            <option value="1" {{ ($general->free_user_can_claim_red_bag ?? 0) == 1 ? 'selected' : '' }}>@lang('Yes')</option>
                            <option value="0" {{ ($general->free_user_can_claim_red_bag ?? 0) == 0 ? 'selected' : '' }}>@lang('No')</option>
                        </select>
                        <small class="text-muted">@lang('Allow free users to claim red bag rewards')</small>
                    </div>

                    <button type="submit" class="btn btn--primary w-100 h-45">
                        <i class="las la-save"></i> @lang('Update Settings')
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Step Commission Preview Card -->
    <div class="col-lg-6 mb-30">
        <div class="card b-radius--10">
            <div class="card-header bg--success">
                <h5 class="card-title text-white">
                    <i class="las la-layer-group"></i> @lang('Step Commission Preview')
                </h5>
            </div>
            <div class="card-body">
                @php
                    $baseAmt = $general->free_user_step_base_amount ?? 100;
                    $incAmt = $general->free_user_step_increment ?? 100;
                    $maxSteps = $general->free_user_step_max ?? 10;
                @endphp

                @if($general->free_user_step_commission_enabled ?? 0)
                <div class="alert alert-success">
                    <i class="las la-check-circle"></i> @lang('Step Commission is ENABLED')
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center">@lang('Referral #')</th>
                                <th class="text-center">@lang('Commission')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= min($maxSteps, 5); $i++)
                            <tr>
                                <td class="text-center"><span class="badge bg-primary">{{ $i }}@lang('st/nd/rd/th')</span></td>
                                <td class="text-center fw-bold text-success">৳{{ showAmount($baseAmt + (($i - 1) * $incAmt)) }}</td>
                            </tr>
                            @endfor
                            @if($maxSteps > 5)
                            <tr>
                                <td class="text-center" colspan="2">...</td>
                            </tr>
                            <tr>
                                <td class="text-center"><span class="badge bg-warning">{{ $maxSteps }}@lang('th (Max)')</span></td>
                                <td class="text-center fw-bold text-success">৳{{ showAmount($baseAmt + (($maxSteps - 1) * $incAmt)) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 p-3 bg-light rounded">
                    <strong>@lang('Formula'):</strong>
                    <code>৳{{ showAmount($baseAmt) }} + (Step - 1) × ৳{{ showAmount($incAmt) }}</code>
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="las la-exclamation-triangle"></i> @lang('Step Commission is DISABLED')
                    <p class="mb-0 mt-2 small">@lang('Enable it from the settings on the left to use progressive referral commissions.')</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Info Box -->
<div class="row">
    <div class="col-12">
        <div class="card b-radius--10">
            <div class="card-header bg--dark">
                <h5 class="card-title text-white">
                    <i class="las la-info-circle"></i> @lang('Free vs Premium User System')
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted"><i class="las la-user text-primary"></i> @lang('Free Users')</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="las la-check text-success"></i> @lang('Users without any active subscription plan')</li>
                            <li><i class="las la-check text-success"></i> @lang('Step-based progressive referral commission')</li>
                            <li><i class="las la-check text-success"></i> @lang('1st referral = Base, 2nd = Base + Inc, etc.')</li>
                            <li><i class="las la-check text-success"></i> @lang('Limited daily withdrawal')</li>
                            <li><i class="las la-check text-success"></i> @lang('Limited PTC views per day')</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted"><i class="las la-crown text-warning"></i> @lang('Premium Users')</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="las la-check text-success"></i> @lang('Users with active subscription plan')</li>
                            <li><i class="las la-check text-success"></i> @lang('Percentage-based referral commissions')</li>
                            <li><i class="las la-check text-success"></i> @lang('Deposit, Task, Plan commissions')</li>
                            <li><i class="las la-check text-success"></i> @lang('Higher withdrawal limits (as per plan)')</li>
                            <li><i class="las la-check text-success"></i> @lang('Unlimited PTC views (as per plan)')</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
