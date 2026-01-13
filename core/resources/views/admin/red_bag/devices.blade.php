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
                                <th>@lang('Device ID')</th>
                                <th>@lang('First User')</th>
                                <th>@lang('Claim Count')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Block Reason')</th>
                                <th>@lang('First Seen')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devices as $device)
                            <tr class="{{ $device->is_blocked ? 'bg-danger-light' : '' }}">
                                <td>
                                    <small class="text-muted">{{ Str::limit($device->device_id, 25) }}</small>
                                </td>
                                <td>
                                    @if($device->firstUser)
                                        <a href="{{ route('admin.users.detail', $device->first_user_id) }}">
                                            {{ $device->firstUser->username }}
                                        </a>
                                    @else
                                        <span class="text-muted">@lang('Unknown')</span>
                                    @endif
                                </td>
                                <td>{{ number_format($device->claim_count) }}</td>
                                <td>
                                    @if($device->is_blocked)
                                        <span class="badge badge--danger">@lang('Blocked')</span>
                                    @else
                                        <span class="badge badge--success">@lang('Active')</span>
                                    @endif
                                </td>
                                <td>{{ $device->block_reason ?? '-' }}</td>
                                <td>{{ showDateTime($device->created_at, 'd M, Y') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline--{{ $device->is_blocked ? 'success' : 'danger' }}"
                                            data-bs-toggle="modal" data-bs-target="#blockModal"
                                            data-id="{{ $device->id }}" data-blocked="{{ $device->is_blocked }}">
                                        <i class="la la-{{ $device->is_blocked ? 'unlock' : 'lock' }}"></i>
                                        {{ $device->is_blocked ? 'Unblock' : 'Block' }}
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
            @if($devices->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($devices) }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Block Modal --}}
<div class="modal fade" id="blockModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Block/Unblock Device')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="" method="POST" id="blockForm">
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you sure you want to change this device\'s block status?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--primary">@lang('Confirm')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <form action="" method="GET" class="d-flex gap-2">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="blocked_only" value="1"
                   {{ request()->blocked_only ? 'checked' : '' }} onchange="this.form.submit()">
            <label class="form-check-label">@lang('Blocked Only')</label>
        </div>
    </form>
    <a href="{{ route('admin.red-bag.statistics') }}" class="btn btn-sm btn-outline--success">
        <i class="las la-chart-bar"></i>@lang('Statistics')
    </a>
    <a href="{{ route('admin.red-bag.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-arrow-left"></i>@lang('Back')
    </a>
@endpush

@push('script')
<script>
    (function($){
        "use strict";

        $('#blockModal').on('show.bs.modal', function(e) {
            var btn = $(e.relatedTarget);
            var id = btn.data('id');
            $('#blockForm').attr('action', '{{ route("admin.red-bag.block-device", "") }}/' + id);
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
