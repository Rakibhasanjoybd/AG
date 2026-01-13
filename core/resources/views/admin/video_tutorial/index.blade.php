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
                                <th>@lang('Thumbnail')</th>
                                <th>@lang('Lesson')</th>
                                <th>@lang('Title')</th>
                                <th>@lang('Order')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tutorials as $item)
                            <tr>
                                <td>
                                    <img src="{{ getImage(getFilePath('tutorial').'/'.$item->thumbnail, '60x40') }}" alt="" class="rounded" width="60">
                                </td>
                                <td>{{ $item->lesson_number }}</td>
                                <td>{{ Str::limit($item->title, 30) }}</td>
                                <td>{{ $item->order }}</td>
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
                                        data-video_url="{{ $item->video_url }}"
                                        data-description="{{ $item->description }}"
                                        data-lesson_number="{{ $item->lesson_number }}"
                                        data-order="{{ $item->order }}"
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
            @if($tutorials->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($tutorials) }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add Video Tutorial')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="{{ route('admin.tutorial.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Video URL')</label>
                                <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/..." required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Lesson Number')</label>
                                <input type="number" name="lesson_number" class="form-control" value="1" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Order')</label>
                                <input type="number" name="order" class="form-control" value="0" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Description')</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Thumbnail')</label>
                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Status')</label><br>
                                <input type="checkbox" data-toggle="toggle" data-on="@lang('Active')" data-off="@lang('Inactive')" data-onstyle="success" data-offstyle="danger" name="status" checked>
                            </div>
                        </div>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Video Tutorial')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="editForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Title')</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Video URL')</label>
                                <input type="url" name="video_url" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Lesson Number')</label>
                                <input type="number" name="lesson_number" class="form-control" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Order')</label>
                                <input type="number" name="order" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Description')</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Thumbnail')</label>
                                <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Status')</label><br>
                                <input type="checkbox" data-toggle="toggle" data-on="@lang('Active')" data-off="@lang('Inactive')" data-onstyle="success" data-offstyle="danger" name="status">
                            </div>
                        </div>
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
                    <p>@lang('Are you sure you want to delete this tutorial?')</p>
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
        modal.find('[name=video_url]').val(data.video_url);
        modal.find('[name=description]').val(data.description);
        modal.find('[name=lesson_number]').val(data.lesson_number);
        modal.find('[name=order]').val(data.order);
        if(data.status) {
            modal.find('[name=status]').bootstrapToggle('on');
        } else {
            modal.find('[name=status]').bootstrapToggle('off');
        }
        $('#editForm').attr('action', '{{ route("admin.tutorial.update", "") }}/' + data.id);
        modal.modal('show');
    });

    $('.deleteBtn').on('click', function(){
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '{{ route("admin.tutorial.delete", "") }}/' + id);
        $('#deleteModal').modal('show');
    });

    $('.statusBtn').on('click', function(){
        var id = $(this).data('id');
        $.post('{{ route("admin.tutorial.status", "") }}/' + id, {_token: '{{ csrf_token() }}'}, function(){
            location.reload();
        });
    });

})(jQuery);
</script>
@endpush
