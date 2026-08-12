@php
    $logoPath = setting('app_logo') ?? setting('system_logo');
    $appName = setting('app_name', config('app.name', 'PharmCare'));
@endphp

@if(!empty($logoPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath))
    <img src="{{ asset('media/' . $logoPath) }}" alt="{{ $appName }}" {{ $attributes->merge(['class' => 'object-contain h-12 w-auto']) }}>
@else
    <div {{ $attributes->merge(['class' => 'flex items-center justify-center bg-emerald-600 text-white rounded-xl shadow-sm p-2']) }}>
        <svg class="w-full h-full" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="24" height="24" rx="6" fill="#10B981"/>
            <path d="M12 7V17M7 12H17" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
@endif
