@extends('admin.layouts.app')

@section('panel')
    <div class="row justify-content-center">
        <div class="col-xl-3 col-sm-6 mb-30">
            <div class="widget-two box--shadow2 has-link b-radius--5 bg--primary">
                <a href="{{ route('admin.premium.commission.index') }}" class="item-link"></a>
                <div class="widget-two__content">
                    <h2 class="text-white">{{ \App\Models\PremiumReferralCommission::count() }}</h2>
                    <p class="text-white">@lang('All Commissions')</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-30">
            <div class="widget-two box--shadow2 has-link b-radius--5 bg--warning">
                <a href="{{ route('admin.premium.commission.pending') }}" class="item-link"></a>
                <div class="widget-two__content">
                    <h2 class="text-white">{{ \App\Models\PremiumReferralCommission::where('status', 'pending')->count() }}</h2>
                    <p class="text-white">@lang('Pending')</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-30">
            <div class="widget-two box--shadow2 has-link b-radius--5 bg--success">
                <a href="{{ route('admin.premium.commission.approved') }}" class="item-link"></a>
                <div class="widget-two__content">
                    <h2 class="text-white">{{ \App\Models\PremiumReferralCommission::where('status', 'approved')->count() }}</h2>
                    <p class="text-white">@lang('Approved')</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-30">
            <div class="widget-two box--shadow2 has-link b-radius--5 bg--danger">
                <a href="{{ route('admin.premium.commission.locked') }}" class="item-link"></a>
                <div class="widget-two__content">
                    <h2 class="text-white">{{ \App\Models\PremiumReferralCommission::where('status', 'locked')->count() }}</h2>
                    <p class="text-white">@lang('Locked')</p>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="card b-radius--10">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Referrer')</th>
                                    <th>@lang('Referred User')</th>
                                    <th>@lang('Plan')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($commissions as $commission)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $commission->referrer->fullname ?? 'N/A' }}</span>
                                            <br>
                                            <small><a href="{{ route('admin.users.detail', $commission->referrer_id) }}">@{{ $commission->referrer->username ?? 'N/A' }}</a></small>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $commission->referredUser->fullname ?? 'N/A' }}</span>
                                            <br>
                                            <small><a href="{{ route('admin.users.detail', $commission->referred_user_id) }}">@{{ $commission->referredUser->username ?? 'N/A' }}</a></small>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $commission->plan->name ?? 'N/A' }}</span>
                                            <br>
                                            <small>Package #{{ $commission->package_number }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">{{ $general->cur_sym }}{{ showAmount($commission->amount) }}</span>
                                        </td>
                                        <td>
                                            @if($commission->status == 'pending')
                                                <span class="badge badge--warning">@lang('Pending')</span>
                                            @elseif($commission->status == 'approved')
                                                <span class="badge badge--success">@lang('Approved')</span>
                                            @elseif($commission->status == 'locked')
                                                <span class="badge badge--danger">@lang('Locked')</span>
                                            @elseif($commission->status == 'reversed')
                                                <span class="badge badge--dark">@lang('Reversed')</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ showDateTime($commission->created_at) }}
                                            <br>
                                            <small>{{ diffForHumans($commission->created_at) }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                @if($commission->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn"
                                                        data-action="{{ route('admin.premium.commission.approve', $commission->id) }}"
                                                        data-question="@lang('Are you sure to approve this commission?')">
                                                        <i class="la la-check"></i> @lang('Approve')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--danger lockBtn"
                                                        data-id="{{ $commission->id }}"
                                                        data-action="{{ route('admin.premium.commission.lock', $commission->id) }}">
                                                        <i class="la la-lock"></i> @lang('Lock')
                                                    </button>
                                                @elseif($commission->status == 'locked')
                                                    <button type="button" class="btn btn-sm btn-outline--success confirmationBtn"
                                                        data-action="{{ route('admin.premium.commission.approve', $commission->id) }}"
                                                        data-question="@lang('Are you sure to approve this locked commission?')">
                                                        <i class="la la-check"></i> @lang('Approve')
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline--warning reverseBtn"
                                                        data-id="{{ $commission->id }}"
                                                        data-action="{{ route('admin.premium.commission.reverse', $commission->id) }}">
                                                        <i class="la la-undo"></i> @lang('Reverse')
                                                    </button>
                                                @elseif($commission->status == 'approved')
                                                    <button type="button" class="btn btn-sm btn-outline--warning reverseBtn"
                                                        data-id="{{ $commission->id }}"
                                                        data-action="{{ route('admin.premium.commission.reverse', $commission->id) }}">
                                                        <i class="la la-undo"></i> @lang('Reverse')
                                                    </button>
                                                @endif
                                                @if($commission->notes)
                                                    <button type="button" class="btn btn-sm btn-outline--info" data-bs-toggle="tooltip" title="{{ $commission->notes }}">
                                                        <i class="la la-sticky-note"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%" class="text-center">{{ __($emptyMessage ?? 'No data found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($commissions->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($commissions) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Lock Modal --}}
    <div class="modal fade" id="lockModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Lock Commission')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <i class="la la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="lockForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Notes (Optional)')</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="@lang('Enter reason for locking')"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--danger">@lang('Lock')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reverse Modal --}}
    <div class="modal fade" id="reverseModal" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reverse Commission')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal">
                        <i class="la la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="reverseForm">
                    @csrf
                    <div class="modal-body">
                        <p class="text-warning">@lang('Warning: If this commission was approved, the amount will be deducted from the referrer\'s balance.')</p>
                        <div class="form-group">
                            <label>@lang('Notes (Optional)')</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="@lang('Enter reason for reversing')"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                        <button type="submit" class="btn btn--warning">@lang('Reverse')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <form action="" method="GET" class="d-flex">
        <div class="input-group">
            <input type="text" name="search" class="form-control bg--white" placeholder="@lang('Username / Email')" value="{{ request()->search }}">
            <button class="btn btn--primary input-group-text" type="submit"><i class="fa fa-search"></i></button>
        </div>
    </form>
@endpush

@push('script')
<script>
    (function ($) {
        "use strict";

        $('.lockBtn').on('click', function () {
            var action = $(this).data('action');
            $('#lockForm').attr('action', action);
            $('#lockModal').modal('show');
        });

        $('.reverseBtn').on('click', function () {
            var action = $(this).data('action');
            $('#reverseForm').attr('action', action);
            $('#reverseModal').modal('show');
        });

    })(jQuery);
</script>
@endpush
