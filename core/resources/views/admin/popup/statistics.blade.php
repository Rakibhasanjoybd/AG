@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary">
                    <div class="card-body text-white text-center">
                        <h3 class="mb-1">{{ $popup->viewedByUsers->count() }}</h3>
                        <span>@lang('Total Views')</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success">
                    <div class="card-body text-white text-center">
                        <h3 class="mb-1">{{ $popup->target_type == 'all' ? __('All Users') : $popup->targetUsers->count() . ' ' . __('Users') }}</h3>
                        <span>@lang('Target Audience')</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info">
                    <div class="card-body text-white text-center">
                        <h3 class="mb-1">{{ $popup->status ? __('Active') : __('Inactive') }}</h3>
                        <span>@lang('Status')</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('User')</th>
                                <th>@lang('Email')</th>
                                <th>@lang('Viewed At')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($views as $view)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $view->username }}</span>
                                    <br>
                                    <small class="text-muted">{{ $view->fullname }}</small>
                                </td>
                                <td>{{ $view->email }}</td>
                                <td>{{ showDateTime($view->pivot->viewed_at, 'd M, Y H:i:s') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">@lang('No views recorded yet')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($views->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($views) }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<a href="{{ route('admin.popup.index') }}" class="btn btn-sm btn-outline--primary">
    <i class="la la-arrow-left"></i> @lang('Back to List')
</a>
<form action="{{ route('admin.popup.resetViews', $popup->id) }}" method="POST" class="d-inline">
    @csrf
    <button type="submit" class="btn btn-sm btn-outline--warning" onclick="return confirm('@lang('Are you sure?')')">
        <i class="la la-redo-alt"></i> @lang('Reset All Views')
    </button>
</form>
@endpush
