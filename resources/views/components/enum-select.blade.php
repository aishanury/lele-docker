@props(['enum', 'currentValue' => null])

<select {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
    @foreach ($enum::cases() as $case)
        <option value="{{ $case->value }}" {{ $currentValue == $case->value ? 'selected' : '' }}>{{ $case->label() }}</option>
    @endforeach
</select>
