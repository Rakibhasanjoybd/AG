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
                                <th>@lang('Title')</th>
                                <th>@lang('Target')</th>
                                <th>@lang('Priority')</th>
                                <th>@lang('Date Range')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($popups as $popup)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($popup->image)
                                            @php
                                                $isExternalUrl = filter_var($popup->image, FILTER_VALIDATE_URL);
                                                $thumbUrl = $isExternalUrl ? $popup->image : getImage('assets/images/popup/' . $popup->image);
                                            @endphp
                                            <img src="{{ $thumbUrl }}" 
                                                 alt="{{ $popup->title }}" 
                                                 class="me-2" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        @else
                                            <div class="me-2 bg-primary d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; border-radius: 8px;">
                                                <i class="las la-bullhorn text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <span class="fw-bold">{{ Str::limit($popup->title, 30) }}</span>
                                            @if($popup->button_text)
                                                <br><small class="text-muted"><i class="las la-link"></i> {{ $popup->button_text }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($popup->target_type == 'all')
                                        <span class="badge badge--primary"><i class="las la-users"></i> @lang('All Users')</span>
                                    @else
                                        <span class="badge badge--info"><i class="las la-user-check"></i> {{ $popup->targetUsers->count() }} @lang('Users')</span>
                                    @endif
                                    @if($popup->show_to_guests)
                                        <br><small class="text-warning"><i class="las la-user-secret"></i> @lang('+ Guests')</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge--dark">{{ $popup->priority }}</span>
                                </td>
                                <td>
                                    @if($popup->start_date || $popup->end_date)
                                        <small>
                                            @if($popup->start_date)
                                                <i class="las la-calendar-plus text-success"></i> {{ showDateTime($popup->start_date, 'd M, Y') }}
                                            @else
                                                <span class="text-muted">@lang('No start')</span>
                                            @endif
                                            <br>
                                            @if($popup->end_date)
                                                <i class="las la-calendar-minus text-danger"></i> {{ showDateTime($popup->end_date, 'd M, Y') }}
                                            @else
                                                <span class="text-muted">@lang('No end')</span>
                                            @endif
                                        </small>
                                    @else
                                        <span class="text-muted">@lang('Always')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($popup->status)
                                        <span class="badge badge--success">@lang('Active')</span>
                                    @else
                                        <span class="badge badge--danger">@lang('Inactive')</span>
                                    @endif
                                    @if($popup->show_once)
                                        <br><small class="text-info"><i class="las la-eye-slash"></i> @lang('Show Once')</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.popup.edit', $popup->id) }}" 
                                           class="btn btn-sm btn-outline--primary" title="@lang('Edit')">
                                            <i class="la la-pencil"></i>
                                        </a>
                                        <a href="{{ route('admin.popup.statistics', $popup->id) }}" 
                                           class="btn btn-sm btn-outline--info" title="@lang('Statistics')">
                                            <i class="la la-chart-bar"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline--{{ $popup->status ? 'danger' : 'success' }} statusBtn" 
                                                data-id="{{ $popup->id }}" title="@lang('Toggle Status')">
                                            <i class="la la-{{ $popup->status ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline--dark duplicateBtn" 
                                                data-id="{{ $popup->id }}" title="@lang('Duplicate')">
                                            <i class="la la-copy"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline--warning resetBtn" 
                                                data-id="{{ $popup->id }}" title="@lang('Reset Views')">
                                            <i class="la la-redo-alt"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline--danger deleteBtn" 
                                                data-id="{{ $popup->id }}" title="@lang('Delete')">
                                            <i class="la la-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No popup announcements found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($popups->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($popups) }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Delete Confirmation')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="" method="POST" id="deleteForm">
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you sure you want to delete this popup announcement?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--danger">@lang('Delete')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Views Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Reset Views Confirmation')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="" method="POST" id="resetForm">
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you sure you want to reset views for this popup? This will make the popup visible again to all users who have already seen it.')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--warning">@lang('Reset Views')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<a href="{{ route('admin.popup.create') }}" class="btn btn-sm btn-outline--primary">
    <i class="la la-plus"></i> @lang('Add New')
</a>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    $('.deleteBtn').on('click', function(){
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '{{ route("admin.popup.delete", "") }}/' + id);
        $('#deleteModal').modal('show');
    });

    $('.statusBtn').on('click', function(){
        var id = $(this).data('id');
        $.post('{{ route("admin.popup.status", "") }}/' + id, {_token: '{{ csrf_token() }}'}, function(){
            location.reload();
        });
    });

    $('.duplicateBtn').on('click', function(){
        var id = $(this).data('id');
        $.post('{{ route("admin.popup.duplicate", "") }}/' + id, {_token: '{{ csrf_token() }}'}, function(){
            location.reload();
        });
    });

    $('.resetBtn').on('click', function(){
        var id = $(this).data('id');
        $('#resetForm').attr('action', '{{ route("admin.popup.resetViews", "") }}/' + id);
        $('#resetModal').modal('show');
    });
})(jQuery);
</script>
@endpush
