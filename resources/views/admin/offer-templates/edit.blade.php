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

      {{-- Medias --}}
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
          @php
            $medias = $offerTemplate->medias ?? [];
            if (is_string($medias)) {
                $medias = json_decode($medias, true) ?: [];
            }
          @endphp

          @foreach ($medias as $index => $media)
            <div class="row g-2 align-items-end media-row mb-2" data-id="{{ $index }}">
              <div class="col-md-3">
                <label class="form-label">{{ __('Media Title') }}</label>
                <input type="text" name="medias[{{ $index }}][title]" class="form-control"
                       placeholder="Enter title" value="{{ $media['title'] ?? '' }}">
              </div>
              <div class="col-md-8">
                <label class="form-label">{{ __('Media Link') }}</label>
                <input type="url" name="medias[{{ $index }}][link]" class="form-control"
                       placeholder="https://example.com" value="{{ $media['link'] ?? '' }}">
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-danger remove-media-btn w-100 {{ $index == 0 ? 'd-none' : '' }}">
                  <i class="ri-delete-bin-line"></i>
                </button>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Levels --}}
      <div class="row mt-3">
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

      {{-- Currency --}}
      <x-data-entry.input type="text" name="currency" label="Currency"
                          value="{{ old('currency', $offerTemplate->currency ?? 'USD') }}" required disabled />

      {{-- Region --}}
      <x-data-entry.input type="text" name="region" label="Region"
                          value="{{ old('region', $offerTemplate->region ?? 'Global') }}" disabled />

      {{-- Delivery Method --}}
      @php
        $delivery = old(
            'delivery_method',
            $offerTemplate->delivery_method ?? [
                'quantity_from' => '',
                'speed_hour' => '',
                'speed_min' => '',
            ],
        );
      @endphp


      <div class="card mt-3 border-0 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0">
            <i class="ri-truck-line text-primary me-1"></i> {{ __('Delivery Method') }}
          </h5>
        </div>
        <div class="card-body">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="delivery_method_type" value="manual" checked disabled>
            <label class="form-check-label">{{ __('Manual') }}</label>
          </div>

          <div class="row mt-2">
            <div class="col-md-4">
              <x-data-entry.input type="number" name="delivery_quantity_from" label="Delivery Quantity From"
                                  value="{{ $delivery['quantity_from'] ?? '' }}" required disabled />
            </div>
            <div class="col-md-4">
              <x-data-entry.input type="number" name="delivery_speed_hour" label="Delivery Speed (Hour)"
                                  value="{{ $delivery['speed_hour'] ?? '' }}" required />
            </div>
            <div class="col-md-4">
              <x-data-entry.input type="number" name="delivery_speed_min" label="Delivery Speed (Minute)"
                                  value="{{ $delivery['speed_min'] ?? '' }}" required />
            </div>
          </div>
        </div>
      </div>

    </x-data-entry.form>
  </x-data-display.card>

  @push('scripts')
    <script>
      $(document).ready(function() {
        let mediaIndex = {{ count($medias) }};

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

        $('#add-media-btn').on('click', function() {
          $('#media-wrapper').append(createMediaRow(mediaIndex));
          mediaIndex++;
        });

        $('#media-wrapper').on('click', '.remove-media-btn', function() {
          $(this).closest('.media-row').remove();
        });
      });
    </script>
  @endpush

</x-layouts.admin.master>
