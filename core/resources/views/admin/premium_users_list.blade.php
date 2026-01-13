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
                                <th>@lang('Plan')</th>
                                <th>@lang('Balance')</th>
                                <th>@lang('Referrals')</th>
                                <th>@lang('Expires')</th>
                                <th>@lang('Joined')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <span class="fw-bold">{{ $user->fullname }}</span>
                                    <br>
                                    <span class="small text-muted">
                                        <a href="{{ route('admin.users.detail', $user->id) }}">{{ '@'.$user->username }}</a>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge--success">{{ $user->plan->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold">৳{{ showAmount($user->balance) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge--primary">{{ \App\Models\User::where('ref_by', $user->id)->count() }}</span>
                                </td>
                                <td>
                                    @if($user->expire_date)
                                        <span class="{{ $user->expire_date >= now() ? 'text-success' : 'text-danger' }}">
                                            {{ showDateTime($user->expire_date, 'd M, Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ showDateTime($user->created_at, 'd M, Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.users.detail', $user->id) }}" class="btn btn-sm btn-outline--primary">
                                        <i class="las la-desktop"></i> @lang('Details')
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No premium users found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($users) }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
