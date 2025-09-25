<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Edit Level') }}</h5>
        <x-action.link href="{{ route('levels.index') }}" icon="ri-list-check">
          {{ __('Level List') }}
        </x-action.link>
      </div>
    </x-slot>

    <x-data-entry.form action="{{ route('levels.update', $level->id) }}" :model="$level">
      {{-- Level Type --}}
      <x-data-entry.select name="level_type" label="Level Type" :options="[
          'Town Hall' => 'Town Hall',
          'King' => 'King',
          'Queen' => 'Queen',
          'Warden' => 'Warden',
          'Champion' => 'Champion',
      ]" :selected="old('level_type', $level->type)"
                           placeholder="Select Level Type" required />

      {{-- Level Value --}}
      <x-data-entry.input type="text" name="level_value" label="Level Value" :value="old('level_value', $level->value)"
                          placeholder="Enter level value (e.g., 17, 85+)" required />

      {{-- Active Status --}}
      <x-data-entry.input type="checkbox" name="active" label="Active" :checked="old('active', $level->active)" />
    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>
