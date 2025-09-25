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


      <div class="row">
        <div class="col-md-6">
          <x-data-entry.select name="th_level" label="Town Hall Level" :options="$thLevels"
                               placeholder="Select TH Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="king_level" label="King Level" :options="$kingLevels" placeholder="Select King Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="queen_level" label="Queen Level" :options="$queenLevels"
                               placeholder="Select Queen Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="warden_level" label="Warden Level" :options="$wardenLevels"
                               placeholder="Select Warden Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="champion_level" label="Champion Level" :options="$championLevels"
                               placeholder="Select Champion Level" />
        </div>
      </div>

      <x-data-entry.input type="number" step="0.01" name="price" label="Price" placeholder="Enter price"
                          required />
      <x-data-entry.input type="text" name="currency" label="Currency" placeholder="Currency" value="USD"
                          required />
      <x-data-entry.input type="text" name="region" label="Region" placeholder="Region" value="Global" />

    </x-data-entry.form>
  </x-data-display.card>
</x-layouts.admin.master>
