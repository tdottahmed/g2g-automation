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
      <input type="hidden" name="region" value="Global" />
      <input type="hidden" name="currency" value="USD" />

      <x-data-entry.select name="user_account_id" label="User Account" :options="$userAccounts->pluck('owner_name', 'id')" :selected="old('user_account_id', $offerTemplate->user_account_id ?? null)"
                           placeholder="Select User Account" required />

      <x-data-entry.input type="text" name="title" label="Title" placeholder="Enter title" required />
      <x-data-entry.text-area name="description" label="Description" placeholder="Enter description" rows="3" />
      <div class="card mt-3 border-0 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="ri-play-circle-line text-primary me-1"></i> {{ __('Medias') }}
          </h5>
          <button type="button" class="btn btn-sm btn-primary" id="add-media-btn">
            <i class="ri-add-line me-1"></i> {{ __('Add Media') }}
          </button>
        </div>

        <div class="card-body" id="media-wrapper">
          <div class="row g-2 align-items-end media-row mb-2" data-id="0">
            <div class="col-md-3">
              <label class="form-label">{{ __('Media Title') }}</label>
              <input type="text" name="medias[0][title]" class="form-control" placeholder="Enter title">
            </div>
            <div class="col-md-9">
              <label class="form-label">{{ __('Media Link') }}</label>
              <input type="url" name="medias[0][link]" class="form-control" placeholder="https://example.com">
            </div>

          </div>
        </div>
      </div>


      <div class="row">
        <div class="col-md-6">
          <x-data-entry.select name="th_level" label="Town Hall Level" :options="getLevelsByType('Town Hall')"
                               placeholder="Select TH Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="king_level" label="King Level" :options="getLevelsByType('King')" placeholder="Select King Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="queen_level" label="Queen Level" :options="getLevelsByType('Queen')"
                               placeholder="Select Queen Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="warden_level" label="Warden Level" :options="getLevelsByType('Warden')"
                               placeholder="Select Warden Level" />
        </div>
        <div class="col-md-6">
          <x-data-entry.select name="champion_level" label="Champion Level" :options="getLevelsByType('Champion')"
                               placeholder="Select Champion Level" />
        </div>
      </div>

      <x-data-entry.input type="number" step="0.01" name="price" label="Price" placeholder="Enter price"
                          required />
      <x-data-entry.input type="text" name="currency" label="Currency" placeholder="Currency" value="USD" required
                          disabled />
      <x-data-entry.input type="text" name="region" label="Region" placeholder="Region" value="Global" disabled />

    </x-data-entry.form>
  </x-data-display.card>
  @push('scripts')
    <script>
      $(document).ready(function() {
        let mediaIndex = 1;

        function createMediaRow(index) {
          return `
                <div class="row g-2 align-items-end mb-2 media-row" data-id="${index}">
                    <div class="col-md-3">
                        <label class="form-label">{{ __('Media Title') }}</label>
                        <input type="text" name="medias[${index}][title]" class="form-control" placeholder="Enter title">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">{{ __('Media Link') }}</label>
                        <input type="url" name="medias[${index}][link]" class="form-control" placeholder="https://example.com">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger remove-media-btn w-100">
                            <i class="ri-delete-bin-line"></i> 
                        </button>
                    </div>
                </div>
            `;
        }

        // Add new row
        $('#add-media-btn').on('click', function() {
          $('#media-wrapper').append(createMediaRow(mediaIndex));
          mediaIndex++;
        });

        // Remove row
        $('#media-wrapper').on('click', '.remove-media-btn', function() {
          $(this).closest('.media-row').remove();
        });
      });
    </script>
  @endpush

</x-layouts.admin.master>
