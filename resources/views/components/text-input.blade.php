@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-sky-200 focus:border-sky-400 focus:ring-sky-400 rounded-md shadow-sm']) }}>
