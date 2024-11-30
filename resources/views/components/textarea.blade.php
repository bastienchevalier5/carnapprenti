@props(['label', 'name', 'value' => ''])

<div class="m-5">
    <label for="{{ $name }}" class="block font-medium">{{ $label }}</label>
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        cols="30"
        rows="10"
        class="border border-gray-300 rounded p-2"
    >{{ old($name, $value) }}</textarea>
    @error($name)
        <div class="mt-2">
            <p class="text-red-500 text-sm">{{ $message }}</p>
        </div>
    @enderror
</div>
