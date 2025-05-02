@props(['field'])

@if ($errors->has($field))
    <span class="inline_alert text-red-700">{{ $errors->first($field) }}</span>
@endif
