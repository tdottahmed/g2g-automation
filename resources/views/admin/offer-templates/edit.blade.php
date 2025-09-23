<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Edit Offer Template') }}</h5>
        <x-action.link href="{{ route('offer-templates.index') }}"
                       icon="ri-list-check">{{ __('Offer Template List') }}</x-action.link>
      </div>
    </x-slot>

    <x-data-entry.form action="{{ route('offer-templates.update', $offerTemplate->id) }}" :model="$offerTemplate">
      <x-data-entry.select name="user_account_id" label="User Account" :options="$userAccounts->pluck('owner_name', 'id')" :selected="old('user_account_id', $offerTemplate->user_account_id)"
                           placeholder="Select User Account" required />
      <x-data-entry.input type="text" name="title" label="Title" placeholder="Enter title"
                          value="{{ old('title', $offerTemplate->title) }}" required />

      <x-data-entry.editor name="description" label="Description" placeholder="Enter description">
        {{ old('description', $offerTemplate->description) }}
      </x-data-entry.editor>
      <x-data-entry.input type="number" name="th_level" label="Town Hall Level"
                          value="{{ old('th_level', $offerTemplate->th_level) }}" />
      <x-data-entry.input type="number" name="king_level" label="King Level"
                          value="{{ old('king_level', $offerTemplate->king_level) }}" />
      <x-data-entry.input type="number" name="queen_level" label="Queen Level"
                          value="{{ old('queen_level', $offerTemplate->queen_level) }}" />
      <x-data-entry.input type="number" name="warden_level" label="Warden Level"
                          value="{{ old('warden_level', $offerTemplate->warden_level) }}" />
      <x-data-entry.input type="number" name="champion_level" label="Champion Level"
                          value="{{ old('champion_level', $offerTemplate->champion_level) }}" />

      <x-data-entry.input type="number" step="0.01" name="price" label="Price"
                          value="{{ old('price', $offerTemplate->price) }}" required />

      <x-data-entry.input type="text" name="currency" label="Currency"
                          value="{{ old('currency', $offerTemplate->currency) }}" required />

      <x-data-entry.input type="text" name="region" label="Region"
                          value="{{ old('region', $offerTemplate->region) }}" />

      {{-- <x-data-entry.checkbox name="is_active" label="Active" :checked="old('is_active', $offerTemplate->is_active)" />
      <x-data-entry.checkbox name="instant_delivery" label="Instant Delivery" :checked="old('instant_delivery', $offerTemplate->instant_delivery)" /> --}}
    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>
