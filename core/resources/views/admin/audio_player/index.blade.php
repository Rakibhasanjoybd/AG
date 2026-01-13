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
                                <th>@lang('Title')</th>
                                <th>@lang('Autoplay')</th>
                                <th>@lang('Loop')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($audios as $item)
                            <tr>
                                <td>
                                    <img src="{{ getImage(getFilePath('audioPlayer').'/'.$item->thumbnail, '50x50') }}" alt="" class="rounded" width="50">
                                </td>
                                <td>{{ $item->title }}</td>
                                <td>
                                    @if($item->autoplay)
                                        <span class="badge badge--success">@lang('Yes')</span>
                                    @else
                                        <span class="badge badge--secondary">@lang('No')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->loop)
                                        <span class="badge badge--success">@lang('Yes')</span>
                                    @else
                                        <span class="badge badge--secondary">@lang('No')</span>
                                    @endif
                                </td>
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
                                        data-autoplay="{{ $item->autoplay }}"
                                        data-loop="{{ $item->loop }}"
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
            @if($audios->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($audios) }}
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
                <h5 class="modal-title">@lang('Add Audio')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="{{ route('admin.audio.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Title')</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Audio File') (MP3, WAV, OGG)</label>
                        <input type="file" name="audio_file" class="form-control" accept=".mp3,.wav,.ogg" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Thumbnail')</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Autoplay')</label><br>
                                <input type="checkbox" data-toggle="toggle" data-on="@lang('Yes')" data-off="@lang('No')" data-onstyle="success" data-offstyle="secondary" name="autoplay">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Loop')</label><br>
                                <input type="checkbox" data-toggle="toggle" data-on="@lang('Yes')" data-off="@lang('No')" data-onstyle="success" data-offstyle="secondary" name="loop">
                            </div>
                        </div>
                        <div class="col-md-4">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Edit Audio')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="editForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Title')</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Audio File') (MP3, WAV, OGG) - @lang('Leave empty to keep current')</label>
                        <input type="file" name="audio_file" class="form-control" accept=".mp3,.wav,.ogg">
                    </div>
                    <div class="form-group">
                        <label>@lang('Thumbnail')</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Autoplay')</label><br>
                                <input type="checkbox" data-toggle="toggle" data-on="@lang('Yes')" data-off="@lang('No')" data-onstyle="success" data-offstyle="secondary" name="autoplay">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>@lang('Loop')</label><br>
                                <input type="checkbox" data-toggle="toggle" data-on="@lang('Yes')" data-off="@lang('No')" data-onstyle="success" data-offstyle="secondary" name="loop">
                            </div>
                        </div>
                        <div class="col-md-4">
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
                    <p>@lang('Are you sure you want to delete this audio?')</p>
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
        if(data.autoplay) {
            modal.find('[name=autoplay]').bootstrapToggle('on');
        } else {
            modal.find('[name=autoplay]').bootstrapToggle('off');
        }
        if(data.loop) {
            modal.find('[name=loop]').bootstrapToggle('on');
        } else {
            modal.find('[name=loop]').bootstrapToggle('off');
        }
        if(data.status) {
            modal.find('[name=status]').bootstrapToggle('on');
        } else {
            modal.find('[name=status]').bootstrapToggle('off');
        }
        $('#editForm').attr('action', '{{ route("admin.audio.update", "") }}/' + data.id);
        modal.modal('show');
    });

    $('.deleteBtn').on('click', function(){
        var id = $(this).data('id');
        $('#deleteForm').attr('action', '{{ route("admin.audio.delete", "") }}/' + id);
        $('#deleteModal').modal('show');
    });

    $('.statusBtn').on('click', function(){
        var id = $(this).data('id');
        $.post('{{ route("admin.audio.status", "") }}/' + id, {_token: '{{ csrf_token() }}'}, function(){
            location.reload();
        });
    });

})(jQuery);
</script>
@endpush
