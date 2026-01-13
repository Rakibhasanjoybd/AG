@extends($activeTemplate.'layouts.app')
@section('panel')
    <div class="page-wrapper">
        @if(!request()->routeIs('plans'))
        @include($activeTemplate.'partials.header')
        @endif
        @if(!request()->routeIs('home') && !request()->routeIs('plans'))
        @include($activeTemplate.'partials.breadcrumb')
        @endif
        @yield('content')
    </div>
@endsection
