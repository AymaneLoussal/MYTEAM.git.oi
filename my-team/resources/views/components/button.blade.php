@props(['href'=>null])
@if($href)<a href="{{ $href }}" {{ $attributes->class(['product-cta']) }}>{{ $slot }}</a>
@else<button {{ $attributes->merge(['type'=>'submit'])->class(['product-cta']) }}>{{ $slot }}</button>@endif
