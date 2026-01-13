@extends($activeTemplate . 'layouts.master')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Red Bag History')</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table--responsive--md">
                            <thead>
                                <tr>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Result')</th>
                                    <th>@lang('Amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                <tr>
                                    <td>{{ showDateTime($claim->created_at, 'd M, Y h:i A') }}</td>
                                    <td>
                                        @if($claim->is_winner)
                                            <span class="badge badge--success">@lang('Won')</span>
                                        @else
                                            <span class="badge badge--warning">@lang('Try Again')</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($claim->is_winner)
                                            <span class="text-success fw-bold">+{{ showAmount($claim->amount) }} {{ __($general->cur_text) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">@lang('No red bag claims yet')</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($claims->hasPages())
                <div class="card-footer">
                    {{ paginateLinks($claims) }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
