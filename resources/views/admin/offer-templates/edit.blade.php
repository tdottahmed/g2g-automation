<x-layouts.admin.master>

  <x-data-display.card>
    <x-slot name="header">
      <div class="d-flex justify-content-between align-items-center">
        <h5 class="card-title">{{ __('Edit Offer Template') }}</h5>
        <x-action.link href="{{ route('offer-templates.index') }}"
                       icon="ri-list-check">{{ __('Offer Template List') }}</x-action.link>
      </div>
    </x-slot>

    @php
      $activeGame = old('game', $offerTemplate->game ?? 'clash_of_clans');
      $gd         = $offerTemplate->game_data ?? [];
      $deliveryJson = old('delivery_method', $offerTemplate->delivery_method ?? '{}');
      $delivery     = is_string($deliveryJson) ? json_decode($deliveryJson, true) : $deliveryJson;
      $medias       = $offerTemplate->medias ?? [];
      if (is_string($medias)) { $medias = json_decode($medias, true) ?: []; }
    @endphp

    <x-data-entry.form method="PUT" action="{{ route('offer-templates.update', $offerTemplate->id) }}"
                       :model="$offerTemplate">
      <input type="hidden" name="region" value="Global" />
      <input type="hidden" name="currency" value="USD" />

      {{-- ── Row 1: User Account + Game ──────────────────────────────────────── --}}
      <div class="row">
        <div class="col-md-6">
          <x-data-entry.select name="user_account_id" label="User Account"
                               :options="$userAccounts->pluck('owner_name', 'id')"
                               :selected="old('user_account_id', $offerTemplate->user_account_id)"
                               placeholder="Select User Account" required />
        </div>
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="game">{{ __('Game') }}</label>
            <select id="game" name="game" class="form-control js-example-basic-single" style="width:100%;">
              @foreach ($games as $slug => $label)
                <option value="{{ $slug }}" {{ $activeGame === $slug ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('game') <span class="text-danger">{{ $message }}</span> @enderror
          </div>
        </div>
      </div>

      {{-- ── Game-specific fields ──────────────────────────────────────────────── --}}

      {{-- Clash of Clans --}}
      <fieldset data-game="clash_of_clans" class="game-section p-0 m-0 border-0"
                @if($activeGame !== 'clash_of_clans') style="display:none" disabled @endif>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0"><i class="ri-sword-line me-1"></i> Clash of Clans</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('Town Hall Level') }} <span class="text-danger">*</span></label>
                <input type="number" id="coc-th-level" name="game_data[th_level]"
                       class="form-control" value="{{ old('game_data.th_level', $gd['th_level'] ?? '') }}"
                       placeholder="e.g. 17" min="1" max="17">
                @error('game_data.th_level') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold mb-1">♔ {{ __('Barbarian King') }}</label>
                <input type="number" id="coc-king" name="game_data[king_level]"
                       class="form-control" value="{{ old('game_data.king_level', $gd['king_level'] ?? '') }}"
                       placeholder="0" min="0" max="100">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">♛ {{ __('Archer Queen') }}</label>
                <input type="number" id="coc-queen" name="game_data[queen_level]"
                       class="form-control" value="{{ old('game_data.queen_level', $gd['queen_level'] ?? '') }}"
                       placeholder="0" min="0" max="100">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">⚑ {{ __('Grand Warden') }}</label>
                <input type="number" id="coc-warden" name="game_data[warden_level]"
                       class="form-control" value="{{ old('game_data.warden_level', $gd['warden_level'] ?? '') }}"
                       placeholder="0" min="0" max="65">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">✦ {{ __('Royal Champion') }}</label>
                <input type="number" id="coc-champion" name="game_data[champion_level]"
                       class="form-control" value="{{ old('game_data.champion_level', $gd['champion_level'] ?? '') }}"
                       placeholder="0" min="0" max="50">
              </div>
            </div>
            <div class="mt-2">
              <small class="text-muted"><i class="ri-magic-line me-1"></i>{{ __('Hero levels auto-fill to max for the selected TH — adjust if needed.') }}</small>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- Brawl Stars --}}
      <fieldset data-game="brawl_stars" class="game-section p-0 m-0 border-0"
                @if($activeGame !== 'brawl_stars') style="display:none" disabled @endif>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-warning text-dark py-2">
            <h6 class="mb-0">Brawl Stars</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('Platform') }} <span class="text-danger">*</span></label>
                <select name="game_data[platform]" class="form-select">
                  <option value="">{{ __('Select Platform') }}</option>
                  <option value="Android" {{ old('game_data.platform', $gd['platform'] ?? '') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform', $gd['platform'] ?? '') == 'iOS' ? 'selected' : '' }}>iOS</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Trophies') }} <span class="text-danger">*</span></label>
                <input type="number" name="game_data[trophies]" class="form-control"
                       value="{{ old('game_data.trophies', $gd['trophies'] ?? '') }}"
                       placeholder="e.g. 50000" min="0">
                @error('game_data.trophies') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Brawlers') }} <span class="text-danger">*</span></label>
                <input type="number" name="game_data[brawlers]" class="form-control"
                       value="{{ old('game_data.brawlers', $gd['brawlers'] ?? '') }}"
                       placeholder="Number of brawlers" min="0">
                @error('game_data.brawlers') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Skins') }}</label>
                <input type="number" name="game_data[skins]" class="form-control"
                       value="{{ old('game_data.skins', $gd['skins'] ?? '') }}"
                       placeholder="Number of skins" min="0">
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- Clash Royale --}}
      <fieldset data-game="clash_royale" class="game-section p-0 m-0 border-0"
                @if($activeGame !== 'clash_royale') style="display:none" disabled @endif>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-info text-dark py-2">
            <h6 class="mb-0">Clash Royale</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('King Level') }} <span class="text-danger">*</span></label>
                <input type="number" name="game_data[king_level]" class="form-control"
                       value="{{ old('game_data.king_level', $gd['king_level'] ?? '') }}"
                       placeholder="1–15" min="1" max="15">
                @error('game_data.king_level') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Arena') }} <span class="text-danger">*</span></label>
                <input type="text" name="game_data[arena]" class="form-control"
                       value="{{ old('game_data.arena', $gd['arena'] ?? '') }}"
                       placeholder="e.g. Arena 15, Challenger">
                @error('game_data.arena') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('Level 16 Cards') }}</label>
                <input type="number" name="game_data[level_16_cards]" class="form-control"
                       value="{{ old('game_data.level_16_cards', $gd['level_16_cards'] ?? '') }}"
                       placeholder="Count" min="0">
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('Level 15 Cards') }}</label>
                <input type="number" name="game_data[level_15_cards]" class="form-control"
                       value="{{ old('game_data.level_15_cards', $gd['level_15_cards'] ?? '') }}"
                       placeholder="Count" min="0">
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('Level 14 Cards') }}</label>
                <input type="number" name="game_data[level_14_cards]" class="form-control"
                       value="{{ old('game_data.level_14_cards', $gd['level_14_cards'] ?? '') }}"
                       placeholder="Count" min="0">
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- Hay Day --}}
      <fieldset data-game="hay_day" class="game-section p-0 m-0 border-0"
                @if($activeGame !== 'hay_day') style="display:none" disabled @endif>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-success text-white py-2">
            <h6 class="mb-0">Hay Day</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('Platform') }} <span class="text-danger">*</span></label>
                <select name="game_data[platform]" class="form-select">
                  <option value="">{{ __('Select Platform') }}</option>
                  <option value="Android" {{ old('game_data.platform', $gd['platform'] ?? '') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform', $gd['platform'] ?? '') == 'iOS' ? 'selected' : '' }}>iOS</option>
                  <option value="PC" {{ old('game_data.platform', $gd['platform'] ?? '') == 'PC' ? 'selected' : '' }}>PC</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- Mobile Legends --}}
      <fieldset data-game="mobile_legends" class="game-section p-0 m-0 border-0"
                @if($activeGame !== 'mobile_legends') style="display:none" disabled @endif>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-danger text-white py-2">
            <h6 class="mb-0">Mobile Legends</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('Platform') }} <span class="text-danger">*</span></label>
                <select name="game_data[platform]" class="form-select">
                  <option value="">{{ __('Select Platform') }}</option>
                  <option value="Android" {{ old('game_data.platform', $gd['platform'] ?? '') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform', $gd['platform'] ?? '') == 'iOS' ? 'selected' : '' }}>iOS</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Rank') }} <span class="text-danger">*</span></label>
                <input type="text" name="game_data[rank]" class="form-control"
                       value="{{ old('game_data.rank', $gd['rank'] ?? '') }}"
                       placeholder="e.g. Mythic, Legend">
                @error('game_data.rank') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Heroes') }}</label>
                <input type="number" name="game_data[heroes]" class="form-control"
                       value="{{ old('game_data.heroes', $gd['heroes'] ?? '') }}"
                       placeholder="Number of heroes" min="0">
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Skins') }}</label>
                <input type="number" name="game_data[skins]" class="form-control"
                       value="{{ old('game_data.skins', $gd['skins'] ?? '') }}"
                       placeholder="Number of skins" min="0">
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- Call of Duty Mobile --}}
      <fieldset data-game="call_of_duty_mobile" class="game-section p-0 m-0 border-0"
                @if($activeGame !== 'call_of_duty_mobile') style="display:none" disabled @endif>
        <div class="card border-0 shadow-sm mb-3">
          <div class="card-header bg-dark text-white py-2">
            <h6 class="mb-0">Call of Duty Mobile</h6>
          </div>
          <div class="card-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">{{ __('Platform') }} <span class="text-danger">*</span></label>
                <select name="game_data[platform]" class="form-select">
                  <option value="">{{ __('Select Platform') }}</option>
                  <option value="Android" {{ old('game_data.platform', $gd['platform'] ?? '') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform', $gd['platform'] ?? '') == 'iOS' ? 'selected' : '' }}>iOS</option>
                  <option value="PC" {{ old('game_data.platform', $gd['platform'] ?? '') == 'PC' ? 'selected' : '' }}>PC (Emulator)</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Rank') }} <span class="text-danger">*</span></label>
                <input type="text" name="game_data[rank]" class="form-control"
                       value="{{ old('game_data.rank', $gd['rank'] ?? '') }}"
                       placeholder="e.g. Legendary, Master">
                @error('game_data.rank') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- ── Common offer fields ──────────────────────────────────────────────── --}}
      <x-data-entry.input type="text" name="title" label="Title" placeholder="Enter title"
                          value="{{ old('title', $offerTemplate->title) }}" required />

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

      <x-data-entry.input type="number" step="0.01" name="price" label="Price"
                          value="{{ old('price', $offerTemplate->price) }}" required />

      {{-- Delivery Method --}}
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
                                  value="{{ $delivery['quantity_from'] ?? '1' }}" />
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

      <div class="mt-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_permanent" id="is_permanent" value="1"
                 {{ old('is_permanent', $offerTemplate->is_permanent) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_permanent">
            <i class="ri-shield-check-line me-1 text-success"></i>
            <strong>{{ __('Permanent') }}</strong>
            <span class="text-muted small ms-1">— this offer will be skipped during delete-all runs</span>
          </label>
        </div>
      </div>

    </x-data-entry.form>
  </x-data-display.card>

  @push('scripts')
    <script>
      $(document).ready(function () {

        // ── Game switcher ──────────────────────────────────────────────────────
        function switchGame(game) {
          $('fieldset[data-game]').prop('disabled', true).hide();
          $('fieldset[data-game="' + game + '"]').prop('disabled', false).show();
        }

        $('#game').on('change', function () {
          switchGame($(this).val());
        });

        // ── CoC: auto-fill hero levels from TH max ────────────────────────────
        const cocMax = {
          '3':  { king: 5,   queen: 0,   warden: 0,  champion: 0  },
          '4':  { king: 10,  queen: 0,   warden: 0,  champion: 0  },
          '5':  { king: 10,  queen: 0,   warden: 0,  champion: 0  },
          '6':  { king: 10,  queen: 0,   warden: 0,  champion: 0  },
          '7':  { king: 10,  queen: 0,   warden: 0,  champion: 0  },
          '8':  { king: 20,  queen: 20,  warden: 0,  champion: 0  },
          '9':  { king: 30,  queen: 30,  warden: 0,  champion: 0  },
          '10': { king: 40,  queen: 40,  warden: 20, champion: 0  },
          '11': { king: 50,  queen: 50,  warden: 30, champion: 0  },
          '12': { king: 65,  queen: 65,  warden: 40, champion: 0  },
          '13': { king: 75,  queen: 75,  warden: 50, champion: 15 },
          '14': { king: 80,  queen: 80,  warden: 55, champion: 20 },
          '15': { king: 90,  queen: 90,  warden: 60, champion: 30 },
          '16': { king: 95,  queen: 95,  warden: 65, champion: 45 },
          '17': { king: 100, queen: 100, warden: 65, champion: 50 },
        };

        $('#coc-th-level').on('change', function () {
          const lvl = cocMax[$(this).val()];
          if (lvl) {
            $('#coc-king').val(lvl.king);
            $('#coc-queen').val(lvl.queen);
            $('#coc-warden').val(lvl.warden);
            $('#coc-champion').val(lvl.champion);
          }
        });

        // ── Media rows ────────────────────────────────────────────────────────
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

        $('#add-media-btn').on('click', function () {
          $('#media-wrapper').append(createMediaRow(mediaIndex));
          mediaIndex++;
        });

        $('#media-wrapper').on('click', '.remove-media-btn', function () {
          $(this).closest('.media-row').remove();
        });

      });
    </script>
  @endpush

</x-layouts.admin.master>
