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
                                <th>@lang('Content')</th>
                                <th>@lang('Speed')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $item)
                            <tr>
                                <td>{{ $item->title }}</td>
                                <td>{{ Str::limit($item->content, 50) }}</td>
                                <td>{{ $item->scroll_speed }}ms</td>
                                <td>
                                    @if($item->status)
                                        <span class="badge badge--success">@lang('Active')</span>
                                    @else
                                        <span class="badge badge--danger">@lang('Inactive')</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline--primary editBtn"
                                        data-id="{{ $item->id }}"
                                        data-title="{{ $item->title }}"
                                        data-content="{{ $item->content }}"
                                        data-scroll_speed="{{ $item->scroll_speed }}"
                                        data-status="{{ $item->status }}">
                                        <i class="la la-pencil"></i> @lang('Edit')
                                    </button>
                                    <button class="btn btn-sm btn-outline--{{ $item->status ? 'danger' : 'success' }} statusBtn" data-id="{{ $item->id }}">
                                        <i class="la la-{{ $item->status ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline--danger deleteBtn" data-id="{{ $item->id }}">
                                        <i class="la la-trash"></i>
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
            @if($announcements->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($announcements) }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add Announcement')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="{{ route('admin.announcement.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Title')</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Content')</label>
                        <textarea name="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>@lang('Scroll Speed') (ms)</label>
                        <input type="number" name="scroll_speed" class="form-control" value="50" min="10" max="200" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <input type="checkbox" data-toggle="toggle" data-on="@lang('Active')" data-off="@lang('Inactive')" data-onstyle="success" data-offstyle="danger" name="status" checked>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Announcement')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="" method="POST" id="editForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Title')</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Content')</label>
                        <textarea name="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>@lang('Scroll Speed') (ms)</label>
                        <input type="number" name="scroll_speed" class="form-control" min="10" max="200" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <input type="checkbox" data-toggle="toggle" data-on="@lang('Active')" data-off="@lang('Inactive')" data-onstyle="success" data-offstyle="danger" name="status">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Update')</button>
                </div>
            </form>
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
                    <p>@lang('Are you sure you want to delete this announcement?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--danger">@lang('Delete')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<button class="btn btn-sm btn-outline--primary addBtn"><i class="la la-plus"></i> @lang('Add New')</button>
@endpush

@push('script')
<script>
(function($){
    "use strict";

    $('.addBtn').on('click', function(){
        $('#addModal').modal('show');
    });

    $('.editBtn').on('click', function(){
        var data = $(this).data();
        var modal = $('#editModal');
        modal.find('[name=title]').val(data.title);
        modal.find('[name=content]').val(data.content);
        modal.find('[name=scroll_speed]').val(data.scroll_speed);
        if(data.status) {
            modal.find('[name=status]').bootstrapToggle('on');
        } else {
            modal.find('[name=status]').bootstrapToggle('off');
        }
        $('#editForm').attr('action', '{{ route("admin.announcement.update", "") }}/' + data.id);
        modal.modal('show');
    });

    $('.deleteBtn').on('click', function(){
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '{{ route("admin.announcement.delete", "") }}/' + id);
        $('#deleteModal').modal('show');
    });

    $('.statusBtn').on('click', function(){
        var id = $(this).data('id');
        $.post('{{ route("admin.announcement.status", "") }}/' + id, {_token: '{{ csrf_token() }}'}, function(){
            location.reload();
        });
    });

})(jQuery);
</script>
@endpush
