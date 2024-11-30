@props(['label', 'name', 'type' => 'text', 'value' => ''])

<div class="m-5">
    <label for="{{ $name }}" class="block font-medium">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        class="border border-gray-300 rounded p-2"
        value="{{ old($name, $value) }}"
    />
    @error($name)
        <div class="mt-2">
            <p class="text-red-500 text-sm">{{ $message }}</p>
        </div>
    @enderror
</div>
