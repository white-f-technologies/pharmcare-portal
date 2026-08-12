@props(['disabled' => false])

<div x-data="{ show: false }" class="relative mt-1">
    <input 
        :type="show ? 'text' : 'password'"
        @disabled($disabled) 
        {{ $attributes->merge(['class' => 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm py-2.5 ps-4 pe-11 block w-full text-base font-normal text-gray-900 placeholder-gray-400 focus:outline-none transition duration-150 ease-in-out']) }}
    >
    <button 
        type="button" 
        @click="show = !show" 
        class="absolute inset-y-0 end-0 flex items-center pe-3.5 text-gray-400 hover:text-emerald-600 focus:outline-none transition-colors cursor-pointer"
        tabindex="-1"
        :title="show ? 'Hide password' : 'Show password'"
    >
        <!-- Eye Icon (Click to show password) -->
        <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <!-- Eye Slash Icon (Click to hide password) -->
        <svg x-show="show" class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.025 10.025 0 015.707 1.933M9.88 9.88a3 3 0 104.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
        </svg>
    </button>
</div>
