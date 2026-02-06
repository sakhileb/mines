@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-slate-700 border-slate-600 text-white placeholder-slate-400 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm']) !!}>
