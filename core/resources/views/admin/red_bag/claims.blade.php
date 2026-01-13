@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Red Bag')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Result')</th>
                                <th>@lang('Device ID')</th>
                                <th>@lang('IP Address')</th>
                                <th>@lang('Fraud Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($claims as $claim)
                            <tr class="{{ $claim->is_fraudulent ? 'bg-danger-light' : '' }}">
                                <td>
                                    @if($claim->user)
                                        <a href="{{ route('admin.users.detail', $claim->user_id) }}">
                                            <span class="fw-bold">{{ $claim->user->username }}</span>
                                        </a>
                                        <br><small>{{ $claim->user->email }}</small>
                                    @else
                                        <span class="text-muted">@lang('User Deleted')</span>
                                    @endif
                                </td>
                                <td>{{ $claim->redBag->name ?? 'N/A' }}</td>
                                <td>
                                    @if($claim->is_winner)
                                        <span class="text-success fw-bold">{{ showAmount($claim->amount) }} {{ __($general->cur_text) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($claim->is_winner)
                                        <span class="badge badge--success">@lang('Winner')</span>
                                    @else
                                        <span class="badge badge--warning">@lang('No Win')</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($claim->device_id, 15) }}</small>
                                </td>
                                <td>{{ $claim->ip_address }}</td>
                                <td>
                                    @if($claim->is_fraudulent)
                                        <span class="badge badge--danger">@lang('Fraud')</span>
                                        <br><small class="text-danger">{{ $claim->fraud_reason }}</small>
                                    @else
                                        <span class="badge badge--success">@lang('Clean')</span>
                                    @endif
                                </td>
                                <td>{{ showDateTime($claim->created_at, 'd M, Y h:i A') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline--{{ $claim->is_fraudulent ? 'success' : 'danger' }}"
                                            data-bs-toggle="modal" data-bs-target="#fraudModal"
                                            data-id="{{ $claim->id }}" data-fraud="{{ $claim->is_fraudulent }}">
                                        <i class="la la-{{ $claim->is_fraudulent ? 'check' : 'ban' }}"></i>
                                        {{ $claim->is_fraudulent ? 'Unmark' : 'Mark Fraud' }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No data found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($claims->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($claims) }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Fraud Modal --}}
<div class="modal fade" id="fraudModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Mark as Fraud')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="" method="POST" id="fraudForm">
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you sure? If this was a winning claim, the amount will be reversed from user balance.')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--danger">@lang('Confirm')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <form action="" method="GET" class="d-flex gap-2">
        <div class="input-group w-auto">
            <input type="text" name="search" class="form-control" value="{{ request()->search }}" placeholder="@lang('Search user...')">
            <button class="btn btn--primary" type="submit"><i class="la la-search"></i></button>
        </div>
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="fraud_only" value="1"
                   {{ request()->fraud_only ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label">@lang('Fraud Only')</label>
        </div>
    </form>
    <a href="{{ route('admin.red-bag.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-arrow-left"></i>@lang('Back')
    </a>
@endpush

@push('script')
<script>
    (function($){
        "use strict";

        $('#fraudModal').on('show.bs.modal', function(e) {
            var btn = $(e.relatedTarget);
            var id = btn.data('id');
            $('#fraudForm').attr('action', '{{ route("admin.red-bag.mark-fraud", "") }}/' + id);
        });

    })(jQuery);
</script>
@endpush

@push('style')
<style>
    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
</style>
@endpush
