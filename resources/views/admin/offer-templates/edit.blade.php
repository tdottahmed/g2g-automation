<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Edit Offer Template') }}</h5>
        <x-action.link href="{{ route('offer-templates.index') }}"
                       icon="ri-list-check">{{ __('Offer Template List') }}</x-action.link>
      </div>
    </x-slot>

    <x-data-entry.form method="PUT" action="{{ route('offer-templates.update', $offerTemplate->id) }}"
                       :model="$offerTemplate">
      <input type="hidden" name="region" value="Global" />
      <input type="hidden" name="currency" value="USD" />

      {{-- User Account --}}
      <x-data-entry.select name="user_account_id" label="User Account" :options="$userAccounts->pluck('owner_name', 'id')" :selected="old('user_account_id', $offerTemplate->user_account_id)"
                           placeholder="Select User Account" required />

      {{-- Title --}}
      <x-data-entry.input type="text" name="title" label="Title" placeholder="Enter title"
                          value="{{ old('title', $offerTemplate->title) }}" required />

      {{-- Description --}}
      <x-data-entry.text-area name="description" label="Description" placeholder="Enter description" rows="3"
                              value="{{ old('description', $offerTemplate->description) }}" />

      {{-- Levels --}}
      <div class="row">
        <div class="col-md-6">
          <x-data-entry.select name="th_level" label="Town Hall Level" :options="getLevelsByType('Town Hall')" :selected="old('th_level', $offerTemplate->th_level)"
                               placeholder="Select TH Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="king_level" label="King Level" :options="getLevelsByType('King')" :selected="old('king_level', $offerTemplate->king_level)"
                               placeholder="Select King Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="queen_level" label="Queen Level" :options="getLevelsByType('Queen')" :selected="old('queen_level', $offerTemplate->queen_level)"
                               placeholder="Select Queen Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="warden_level" label="Warden Level" :options="getLevelsByType('Warden')" :selected="old('warden_level', $offerTemplate->warden_level)"
                               placeholder="Select Warden Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="champion_level" label="Champion Level" :options="getLevelsByType('Champion')" :selected="old('champion_level', $offerTemplate->champion_level)"
                               placeholder="Select Champion Level" />
        </div>
      </div>

      {{-- Price --}}
      <x-data-entry.input type="number" step="0.01" name="price" label="Price"
                          value="{{ old('price', $offerTemplate->price) }}" required />

      {{-- Currency (Disabled) --}}
      <x-data-entry.input type="text" name="currency" label="Currency"
                          value="{{ old('currency', $offerTemplate->currency ?? 'USD') }}" required disabled />

      {{-- Region (Disabled) --}}
      <x-data-entry.input type="text" name="region" label="Region"
                          value="{{ old('region', $offerTemplate->region ?? 'Global') }}" disabled />
    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>
