<x-layouts.admin.master>
  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Create Offer Template') }}</h5>
        <x-action.link href="{{ route('offer-templates.index') }}"
                       icon="ri-list-check">{{ __('Offer Template List') }}</x-action.link>
      </div>
    </x-slot>

    <x-data-entry.form action="{{ route('offer-templates.store') }}">
      <x-data-entry.select name="user_account_id" label="User Account" :options="$userAccounts->pluck('owner_name', 'id')" :selected="old('user_account_id', $offerTemplate->user_account_id ?? null)"
                           placeholder="Select User Account" required />


      <x-data-entry.input type="text" name="title" label="Title" placeholder="Enter title" required />
      <x-data-entry.editor name="description" label="Description" placeholder="Enter description" />

      <x-data-entry.input type="number" name="th_level" label="Town Hall Level" placeholder="TH Level" />
      <x-data-entry.input type="number" name="king_level" label="King Level" placeholder="King Level" />
      <x-data-entry.input type="number" name="queen_level" label="Queen Level" placeholder="Queen Level" />
      <x-data-entry.input type="number" name="warden_level" label="Warden Level" placeholder="Warden Level" />
      <x-data-entry.input type="number" name="champion_level" label="Champion Level" placeholder="Champion Level" />

      <x-data-entry.input type="number" step="0.01" name="price" label="Price" placeholder="Enter price"
                          required />
      <x-data-entry.input type="text" name="currency" label="Currency" placeholder="Currency" value="USD"
                          required />
      <x-data-entry.input type="text" name="region" label="Region" placeholder="Region" value="Global" />

      {{-- <x-data-entry.checkbox name="is_active" label="Active" />
      <x-data-entry.checkbox name="instant_delivery" label="Instant Delivery" checked /> --}}
    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>
