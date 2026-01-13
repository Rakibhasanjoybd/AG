@php
    /** @var string $href */
    /** @var bool|null $crossorigin */
    $crossorigin = $crossorigin ?? false;
@endphp

<link rel="preload" href="{{ $href }}" as="style" onload="this.onload=null;this.rel='stylesheet'" @if($crossorigin)crossorigin @endif>
<noscript>
    <link rel="stylesheet" href="{{ $href }}" @if($crossorigin)crossorigin @endif>
</noscript>
