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
                                <th>@lang('Name')</th>
                                <th>@lang('Amount Range')</th>
                                <th>@lang('Daily Limit')</th>
                                <th>@lang('Time Window')</th>
                                <th>@lang('Win Rate')</th>
                                <th>@lang('Budget')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($redBags as $redBag)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $redBag->name }}</span>
                                    @if($redBag->require_referral)
                                        <br><small class="text-muted">@lang('Requires') {{ $redBag->min_referrals }} @lang('referrals')</small>
                                    @endif
                                </td>
                                <td>
                                    {{ showAmount($redBag->min_amount) }} - {{ showAmount($redBag->max_amount) }} {{ __($general->cur_text) }}
                                </td>
                                <td>
                                    {{ $redBag->daily_limit }}/day
                                    @if($redBag->new_user_bonus_count > 0)
                                        <br><small class="text-success">+{{ $redBag->new_user_bonus_count }} @lang('for new users')</small>
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($redBag->start_time)->format('h:i A') }} -
                                    {{ \Carbon\Carbon::parse($redBag->end_time)->format('h:i A') }}
                                </td>
                                <td>{{ $redBag->win_probability }}%</td>
                                <td>
                                    {{ showAmount($redBag->total_daily_budget) }} {{ __($general->cur_text) }}
                                    <br><small class="text-info">@lang('Spent'): {{ showAmount($redBag->spent_today) }}</small>
                                </td>
                                <td>
                                    @php echo $redBag->status ? '<span class="badge badge--success">Active</span>' : '<span class="badge badge--danger">Inactive</span>'; @endphp
                                </td>
                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.red-bag.edit', $redBag->id) }}" class="btn btn-sm btn-outline--primary">
                                            <i class="la la-pencil"></i> @lang('Edit')
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline--{{ $redBag->status ? 'danger' : 'success' }}"
                                                data-bs-toggle="modal" data-bs-target="#statusModal"
                                                data-id="{{ $redBag->id }}" data-status="{{ $redBag->status }}">
                                            <i class="la la-{{ $redBag->status ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline--danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                data-id="{{ $redBag->id }}">
                                            <i class="la la-trash"></i>
                                        </button>
                                    </div>
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
            @if($redBags->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($redBags) }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Status Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Confirmation')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="" method="POST" id="statusForm">
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you sure to change the status?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                    <button type="submit" class="btn btn--primary">@lang('Yes')</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Delete Confirmation')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="" method="POST" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="text-danger">@lang('Are you sure to delete this Red Bag? All claims will also be deleted.')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('No')</button>
                    <button type="submit" class="btn btn--danger">@lang('Yes, Delete')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.red-bag.create') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-plus"></i>@lang('Add New')
    </a>
    <a href="{{ route('admin.red-bag.claims') }}" class="btn btn-sm btn-outline--info">
        <i class="las la-list"></i>@lang('Claims')
    </a>
    <a href="{{ route('admin.red-bag.statistics') }}" class="btn btn-sm btn-outline--success">
        <i class="las la-chart-bar"></i>@lang('Statistics')
    </a>
@endpush

@push('script')
<script>
    (function($){
        "use strict";

        $('#statusModal').on('show.bs.modal', function(e) {
            var btn = $(e.relatedTarget);
            var id = btn.data('id');
            $('#statusForm').attr('action', '{{ route("admin.red-bag.status", "") }}/' + id);
        });

        $('#deleteModal').on('show.bs.modal', function(e) {
            var btn = $(e.relatedTarget);
            var id = btn.data('id');
            $('#deleteForm').attr('action', '{{ route("admin.red-bag.delete", "") }}/' + id);
        });

    })(jQuery);
</script>
@endpush
