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
                                <th>@lang('Logo')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('URL')</th>
                                <th>@lang('Order')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $item)
                            <tr>
                                <td>
                                    <img src="{{ getImage(getFilePath('brand').'/'.$item->image, '80x40') }}" alt="{{ $item->name }}" class="rounded" height="40">
                                </td>
                                <td><strong>{{ $item->name }}</strong></td>
                                <td>
                                    @if($item->url)
                                        <a href="{{ $item->url }}" target="_blank" class="text--primary">
                                            <i class="la la-external-link"></i> {{ Str::limit($item->url, 30) }}
                                        </a>
                                    @else
                                        <span class="text-muted">@lang('N/A')</span>
                                    @endif
                                </td>
                                <td><span class="badge badge--dark">{{ $item->order }}</span></td>
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
                                        data-name="{{ $item->name }}"
                                        data-url="{{ $item->url }}"
                                        data-order="{{ $item->order }}"
                                        data-status="{{ $item->status }}">
                                        <i class="la la-pencil"></i> @lang('Edit')
                                    </button>
                                    <button class="btn btn-sm btn-outline--danger deleteBtn" data-id="{{ $item->id }}">
                                        <i class="la la-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No brands found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($brands->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($brands) }}
            </div>
            @endif
        </div>
    </div>
</div>

<x-confirmation-modal></x-confirmation-modal>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Add New Brand')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="{{ route('admin.agco.brand.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Brand Name')</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Brand URL') <small class="text-muted">(@lang('Optional'))</small></label>
                        <input type="url" name="url" class="form-control" placeholder="https://www.example.com">
                    </div>
                    <div class="form-group">
                        <label>@lang('Display Order')</label>
                        <input type="number" name="order" class="form-control" value="0" min="0" required>
                        <small class="text-muted">@lang('Lower numbers display first')</small>
                    </div>
                    <div class="form-group">
                        <label>@lang('Brand Logo')</label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">@lang('Supported formats: JPG, PNG, GIF, SVG. Max size: 2MB')</small>
                    </div>
                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <input type="checkbox" name="status" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="Active" data-off="Inactive" checked>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--primary">@lang('Save')</button>
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
                <h5 class="modal-title">@lang('Edit Brand')</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><i class="la la-times"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Brand Name')</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>@lang('Brand URL') <small class="text-muted">(@lang('Optional'))</small></label>
                        <input type="url" name="url" class="form-control" placeholder="https://www.example.com">
                    </div>
                    <div class="form-group">
                        <label>@lang('Display Order')</label>
                        <input type="number" name="order" class="form-control" min="0" required>
                        <small class="text-muted">@lang('Lower numbers display first')</small>
                    </div>
                    <div class="form-group">
                        <label>@lang('Brand Logo') <small class="text-muted">(@lang('Leave empty to keep current logo'))</small></label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">@lang('Supported formats: JPG, PNG, GIF, SVG. Max size: 2MB')</small>
                    </div>
                    <div class="form-group">
                        <label>@lang('Status')</label>
                        <input type="checkbox" name="status" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="Active" data-off="Inactive">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--primary">@lang('Update')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <button class="btn btn-sm btn--primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="la la-plus"></i> @lang('Add New')
    </button>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        $('.editBtn').on('click', function() {
            var modal = $('#editModal');
            var data = $(this).data();
            
            modal.find('input[name=name]').val(data.name);
            modal.find('input[name=url]').val(data.url);
            modal.find('input[name=order]').val(data.order);
            modal.find('input[name=status]').bootstrapToggle(data.status ? 'on' : 'off');
            modal.find('form').attr('action', '{{ route("admin.agco.brand.update", "") }}/' + data.id);
            modal.modal('show');
        });

        $('.deleteBtn').on('click', function() {
            var modal = $('#confirmationModal');
            var id = $(this).data('id');
            modal.find('.question').text('Are you sure you want to delete this brand?');
            modal.find('form').attr('action', '{{ route("admin.agco.brand.delete", "") }}/' + id);
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
