<div class="modal fade" id="quickActionModal" tabindex="-1" aria-labelledby="qaModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:500px">
    <div class="modal-content">

      <div class="modal-header">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar-sm flex-shrink-0">
            <div class="avatar-title rounded-circle" id="qa-icon-wrap">
              <i id="qa-icon" class="fs-18"></i>
            </div>
          </div>
          <div>
            <h5 class="modal-title mb-0" id="qaModalTitle">Action</h5>
            <p class="text-muted small mb-0" id="qa-subtitle"></p>
          </div>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
      </div>

      {{-- Step indicator (only visible for 2-step actions) --}}
      <div id="qa-step-bar" class="d-none border-bottom px-4 py-2 bg-body-tertiary">
        <div class="d-flex align-items-center gap-2 small">
          <span class="badge rounded-pill bg-primary" id="qa-pill-1">1</span>
          <span id="qa-step-lbl-1" class="fw-medium">Select Account</span>
          <i class="ri-arrow-right-s-line text-muted"></i>
          <span class="badge rounded-pill border text-muted" id="qa-pill-2">2</span>
          <span id="qa-step-lbl-2" class="text-muted">Select Game</span>
        </div>
      </div>

      <div class="modal-body">
        {{-- Contextual warning --}}
        <div class="alert d-none mb-3" id="qa-warning" role="alert">
          <i class="me-1" id="qa-warn-icon"></i>
          <span id="qa-warn-text"></span>
        </div>

        {{-- Step 1: Account list --}}
        <div id="qa-step-1-body">
          @if ($userAccounts->isEmpty())
            <div class="py-4 text-center text-muted">
              <i class="ri-user-search-line display-5 opacity-25 d-block mb-2"></i>
              <p class="mb-0 small">No accounts found. Add one first.</p>
            </div>
          @else
            <p class="text-muted small fw-semibold mb-2 text-uppercase" style="letter-spacing:.05em">Select an account</p>
            <div class="d-flex flex-column gap-2" id="qa-account-list">
              @foreach ($userAccounts as $account)
                @php
                  $acctGames = ($accountGameCounts[$account->id] ?? collect())->map(fn($g) => [
                    'game'  => $g->game,
                    'label' => \App\Models\OfferTemplate::GAMES[$g->game] ?? $g->game,
                    'count' => (int) $g->game_count,
                  ])->sortByDesc('count')->values()->toArray();

                  $acctAllGames = ($accountAllGameCounts[$account->id] ?? collect())->map(fn($g) => [
                    'game'  => $g->game,
                    'label' => \App\Models\OfferTemplate::GAMES[$g->game] ?? $g->game,
                    'count' => (int) $g->game_count,
                  ])->sortByDesc('count')->values()->toArray();
                @endphp
                <div class="qa-account-card d-flex align-items-center gap-3 p-2 px-3 border rounded-2"
                     role="button"
                     data-id="{{ $account->id }}"
                     data-email="{{ $account->email }}"
                     data-owner="{{ $account->owner_name ?? '' }}"
                     data-total="{{ $account->total_templates }}"
                     data-permanent="{{ $account->permanent_templates_count }}"
                     data-games="{{ json_encode($acctGames) }}"
                     data-all-games="{{ json_encode($acctAllGames) }}">
                  <img src="https://ui-avatars.com/api/?name={{ urlencode($account->email) }}&background=7269ef&color=fff&size=32"
                       class="rounded-circle flex-shrink-0" width="32" height="32" alt="">
                  <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold text-truncate small">{{ $account->email }}</div>
                    @if ($account->owner_name)
                      <div class="text-muted" style="font-size:.75rem">{{ $account->owner_name }}</div>
                    @endif
                  </div>
                  <div class="text-muted small flex-shrink-0">
                    {{ $account->total_templates }} tpl
                  </div>
                  <div class="form-check mb-0 flex-shrink-0">
                    <input class="form-check-input qa-radio" type="radio" name="qa-account-pick" style="pointer-events:none">
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>{{-- /qa-step-1-body --}}

        {{-- Step 2: Game selection --}}
        <div id="qa-step-2-body" class="d-none">
          <div class="alert d-flex align-items-start gap-2 mb-3" id="qa-step-2-alert">
            <i class="mt-1 flex-shrink-0" id="qa-step-2-alert-icon"></i>
            <span id="qa-step-2-alert-text"></span>
          </div>
          <p class="text-muted small fw-semibold mb-2 text-uppercase" style="letter-spacing:.05em">Select a game</p>
          <div class="d-flex flex-column gap-2" id="qa-game-list"></div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="qa-back">
          <i class="ri-arrow-left-s-line me-1"></i>Back
        </button>
        <button type="button" class="btn btn-primary d-none" id="qa-next" disabled>
          Next <i class="ri-arrow-right-s-line ms-1"></i>
        </button>
        <button type="button" class="btn btn-primary" id="qa-confirm" disabled>Confirm</button>
      </div>

    </div>
  </div>
</div>
