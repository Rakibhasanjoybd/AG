@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--primary">
                <i class="las la-gift"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ number_format($stats['total_claims']) }}</h3>
                <p>@lang('Total Claims')</p>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--success">
                <i class="las la-trophy"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ number_format($stats['total_winners']) }}</h3>
                <p>@lang('Total Winners')</p>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--info">
                <i class="las la-money-bill"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ showAmount($stats['total_payout']) }} {{ __($general->cur_text) }}</h3>
                <p>@lang('Total Payout')</p>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--danger">
            <div class="widget-two__icon b-radius--5 bg--danger">
                <i class="las la-ban"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ number_format($stats['fraud_claims']) }}</h3>
                <p>@lang('Fraud Claims')</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--warning">
                <i class="las la-calendar-day"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ number_format($stats['today_claims']) }}</h3>
                <p>@lang('Today Claims')</p>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--teal">
                <i class="las la-coins"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ showAmount($stats['today_payout']) }} {{ __($general->cur_text) }}</h3>
                <p>@lang('Today Payout')</p>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--secondary">
                <i class="las la-mobile"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ number_format($stats['unique_devices']) }}</h3>
                <p>@lang('Unique Devices')</p>
            </div>
        </div>
    </div>
    <div class="col-xxl-3 col-sm-6 mb-30">
        <div class="widget-two box--shadow2 b-radius--5 bg--white">
            <div class="widget-two__icon b-radius--5 bg--dark">
                <i class="las la-lock"></i>
            </div>
            <div class="widget-two__content">
                <h3>{{ number_format($stats['blocked_devices']) }}</h3>
                <p>@lang('Blocked Devices')</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="card-title mb-0">@lang('Daily Statistics (Last 30 Days)')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Date')</th>
                                <th>@lang('Claims')</th>
                                <th>@lang('Payout')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyStats as $stat)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($stat->date)->format('d M, Y') }}</td>
                                <td>{{ number_format($stat->claims) }}</td>
                                <td>{{ showAmount($stat->payout) }} {{ __($general->cur_text) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">@lang('No data found')</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.red-bag.devices') }}" class="btn btn-sm btn-outline--warning">
        <i class="las la-mobile"></i>@lang('Devices')
    </a>
    <a href="{{ route('admin.red-bag.index') }}" class="btn btn-sm btn-outline--primary">
        <i class="las la-arrow-left"></i>@lang('Back')
    </a>
@endpush
