@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg shadow-sm py-2.5 px-4 text-base font-normal text-gray-900 placeholder-gray-400 focus:outline-none transition duration-150 ease-in-out']) }}>
