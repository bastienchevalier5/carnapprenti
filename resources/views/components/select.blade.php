@props(['label', 'name', 'options', 'selected' => null])

<div class="m-5">
    <label for="{{ $name }}" class="block font-medium">{{ $label }}</label>
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        class="border border-gray-300 rounded"
    >
        @foreach($options as $key => $option)
            <option value="{{ $key }}" {{ old($name, $selected) == $key ? 'selected' : '' }}>
                {{ $option }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="mt-2">
            <p class="text-red-500 text-sm">{{ $message }}</p>
        </div>
    @enderror
</div>
