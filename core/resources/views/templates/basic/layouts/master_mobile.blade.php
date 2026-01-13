@extends($activeTemplate.'layouts.app_mobile')
@section('content')
<div class="page-content">
    @include($activeTemplate.'partials.mobile_header')
    @yield('main-content')
</div>
@include($activeTemplate.'partials.mobile_bottom_nav')
@endsection
