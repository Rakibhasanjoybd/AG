@php
    // Lighthouse warning "Uses third-party cookies" is triggered by third-party scripts
    // like GA / chat widgets. Also, modern browsers may block third-party cookies.
    // We only load these extensions in production *after* the user accepts cookies.
    $hasConsent = (bool) \Cookie::get('gdpr_cookie');
@endphp

@if(app()->environment('production') && $hasConsent)
    @php echo loadExtension('tawk-chat') @endphp
    @php echo loadExtension('google-analytics') @endphp
@endif
