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

      @php $activeGame = old('game', 'clash_of_clans'); @endphp

      {{-- ── Row 1: User Accounts + Game ────────────────────────────────────── --}}
      <div class="row">
        <div class="col-md-6">
          <div class="form-group mb-3">
            <label for="user_account_ids" class="form-label">
              {{ __('User Accounts') }} <span class="text-danger">*</span>
            </label>
            <select id="user_account_ids" name="user_account_ids[]"
                    class="form-control select2-accounts" multiple style="width:100%;" required>
              @foreach ($userAccounts as $account)
                <option value="{{ $account->id }}"
                        {{ in_array($account->id, old('user_account_ids', [])) ? 'selected' : '' }}>
                  {{ $account->owner_name }}
                </option>
              @endforeach
            </select>
            @error('user_account_ids')   <span class="text-danger small">{{ $message }}</span> @enderror
            @error('user_account_ids.*') <span class="text-danger small">{{ $message }}</span> @enderror
          </div>
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
                       class="form-control" value="{{ old('game_data.th_level', '') }}"
                       placeholder="e.g. 17">
                @error('game_data.th_level') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label small fw-semibold mb-1">♔ {{ __('Barbarian King') }}</label>
                <input type="number" id="coc-king" name="game_data[king_level]"
                       class="form-control" value="{{ old('game_data.king_level', '') }}"
                       placeholder="0">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">♛ {{ __('Archer Queen') }}</label>
                <input type="number" id="coc-queen" name="game_data[queen_level]"
                       class="form-control" value="{{ old('game_data.queen_level', '') }}"
                       placeholder="0">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">⚑ {{ __('Grand Warden') }}</label>
                <input type="number" id="coc-warden" name="game_data[warden_level]"
                       class="form-control" value="{{ old('game_data.warden_level', '') }}"
                       placeholder="0">
              </div>
              <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">✦ {{ __('Royal Champion') }}</label>
                <input type="number" id="coc-champion" name="game_data[champion_level]"
                       class="form-control" value="{{ old('game_data.champion_level', '') }}"
                       placeholder="0">
              </div>
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
                  <option value="Android" {{ old('game_data.platform') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform') == 'iOS' ? 'selected' : '' }}>iOS</option>
                  <option value="Android & iOS" {{ old('game_data.platform') == 'Android & iOS' ? 'selected' : '' }}>Android & iOS</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Trophies') }} <span class="text-danger">*</span></label>
                <input type="number" name="game_data[trophies]" class="form-control"
                       value="{{ old('game_data.trophies', '') }}" placeholder="e.g. 50000" min="0">
                @error('game_data.trophies') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Brawlers') }} <span class="text-danger">*</span></label>
                <input type="number" name="game_data[brawlers]" class="form-control"
                       value="{{ old('game_data.brawlers', '') }}" placeholder="Number of brawlers" min="0">
                @error('game_data.brawlers') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Skins') }}</label>
                <input type="number" name="game_data[skins]" class="form-control"
                       value="{{ old('game_data.skins', '') }}" placeholder="Number of skins" min="0">
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
                       value="{{ old('game_data.king_level', '') }}" placeholder="1–15">
                @error('game_data.king_level') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Arena') }} <span class="text-danger">*</span></label>
                <input type="text" name="game_data[arena]" class="form-control"
                       value="{{ old('game_data.arena', '') }}" placeholder="e.g. Arena 15, Challenger">
                @error('game_data.arena') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('Level 16 Cards') }}</label>
                <input type="number" name="game_data[level_16_cards]" class="form-control"
                       value="{{ old('game_data.level_16_cards', '') }}" placeholder="Count" min="0">
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('Level 15 Cards') }}</label>
                <input type="number" name="game_data[level_15_cards]" class="form-control"
                       value="{{ old('game_data.level_15_cards', '') }}" placeholder="Count" min="0">
              </div>
              <div class="col-md-4">
                <label class="form-label">{{ __('Level 14 Cards') }}</label>
                <input type="number" name="game_data[level_14_cards]" class="form-control"
                       value="{{ old('game_data.level_14_cards', '') }}" placeholder="Count" min="0">
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
                  <option value="Android" {{ old('game_data.platform') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform') == 'iOS' ? 'selected' : '' }}>iOS</option>
                  <option value="PC" {{ old('game_data.platform') == 'PC' ? 'selected' : '' }}>PC</option>
                  <option value="Android & iOS" {{ old('game_data.platform') == 'Android & iOS' ? 'selected' : '' }}>Android & iOS</option>
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
                  <option value="Android" {{ old('game_data.platform') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform') == 'iOS' ? 'selected' : '' }}>iOS</option>
                  <option value="Android & iOS" {{ old('game_data.platform') == 'Android & iOS' ? 'selected' : '' }}>Android & iOS</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Rank') }} <span class="text-danger">*</span></label>
                <input type="text" name="game_data[rank]" class="form-control"
                       value="{{ old('game_data.rank', '') }}" placeholder="e.g. Mythic, Legend">
                @error('game_data.rank') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Heroes') }}</label>
                <input type="number" name="game_data[heroes]" class="form-control"
                       value="{{ old('game_data.heroes', '') }}" placeholder="Number of heroes" min="0">
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Skins') }}</label>
                <input type="number" name="game_data[skins]" class="form-control"
                       value="{{ old('game_data.skins', '') }}" placeholder="Number of skins" min="0">
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
                  <option value="Android" {{ old('game_data.platform') == 'Android' ? 'selected' : '' }}>Android</option>
                  <option value="iOS" {{ old('game_data.platform') == 'iOS' ? 'selected' : '' }}>iOS</option>
                  <option value="PC" {{ old('game_data.platform') == 'PC' ? 'selected' : '' }}>PC (Emulator)</option>
                  <option value="Android & iOS" {{ old('game_data.platform') == 'Android & iOS' ? 'selected' : '' }}>Android & iOS</option>
                </select>
                @error('game_data.platform') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
              <div class="col-md-6">
                <label class="form-label">{{ __('Rank') }} <span class="text-danger">*</span></label>
                <input type="text" name="game_data[rank]" class="form-control"
                       value="{{ old('game_data.rank', '') }}" placeholder="e.g. Legendary, Master">
                @error('game_data.rank') <span class="text-danger small">{{ $message }}</span> @enderror
              </div>
            </div>
          </div>
        </div>
      </fieldset>

      {{-- ── Common offer fields ──────────────────────────────────────────────── --}}
      <x-data-entry.input type="text" name="title" label="Title" placeholder="Enter title" required />
      <x-data-entry.text-area name="description" label="Description" placeholder="Enter description" rows="3" />

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

      <x-data-entry.input type="number" step="0.01" name="price" label="Price" placeholder="Enter price" required />

      {{-- Delivery Method --}}
      <div class="card mt-3 border-0 shadow-sm">
        <div class="card-header bg-light">
          <h5 class="mb-0"><i class="ri-truck-line text-primary me-1"></i> {{ __('Delivery Method') }}</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="delivery_method" id="delivery_auto" value="automatic" disabled>
              <label class="form-check-label" for="delivery_auto">{{ __('Automatic') }}</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="delivery_method" id="delivery_manual" value="manual" checked disabled>
              <label class="form-check-label" for="delivery_manual">{{ __('Manual') }}</label>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label">{{ __('Delivery Quantity From') }}</label>
              <input type="number" name="delivery_quantity_from" class="form-control"
                     placeholder="Enter quantity" value="{{ old('delivery_quantity_from') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">{{ __('Delivery Speed Hour') }}</label>
              <input type="number" name="delivery_speed_hour" class="form-control"
                     placeholder="Hour" value="{{ old('delivery_speed_hour') }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">{{ __('Delivery Speed Minute') }}</label>
              <input type="number" name="delivery_speed_min" class="form-control"
                     placeholder="Minute" value="{{ old('delivery_speed_min') }}" required>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-3">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_permanent" id="is_permanent" value="1"
                 {{ old('is_permanent') ? 'checked' : '' }}>
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

        // ── User accounts multi-select ─────────────────────────────────────────
        $('#user_account_ids').select2({
          placeholder: 'Select one or more accounts',
          allowClear: true,
          width: '100%',
        });

        // ── Game switcher ──────────────────────────────────────────────────────
        function switchGame(game) {
          $('fieldset[data-game]').prop('disabled', true).hide();
          $('fieldset[data-game="' + game + '"]').prop('disabled', false).show();
        }

        $('#game').on('change', function () {
          switchGame($(this).val());
        });

        // ── Media rows ────────────────────────────────────────────────────────
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
