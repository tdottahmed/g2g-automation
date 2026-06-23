@push('scripts')
<script>
  const CSRF = '{{ csrf_token() }}';

  // ── State ────────────────────────────────────────────────────────────────────
  let currentAccountId    = null;
  let currentPage         = 1;
  let lastMeta            = {};
  let searchDebounceTimer = null;

  // ── Account search (client-side) ─────────────────────────────────────────────
  document.getElementById('account-search')?.addEventListener('input', function () {
    const q       = this.value.toLowerCase().trim();
    const rows    = document.querySelectorAll('.account-row');
    let   visible = 0;

    rows.forEach(row => {
      const match = !q
        || row.dataset.email.includes(q)
        || row.dataset.owner.includes(q);
      row.classList.toggle('d-none', !match);
      if (match) visible++;
    });

    document.getElementById('account-no-results')?.classList.toggle('d-none', visible > 0);
  });

  // ── Open template offcanvas ───────────────────────────────────────────────────
  function openTemplateOffcanvas(accountId, accountEmail, total, permanent) {
    currentAccountId = accountId;
    currentPage      = 1;

    document.getElementById('templatesOffcanvasLabel').textContent = accountEmail;
    document.getElementById('oc-account-meta').textContent =
      `${total} template(s) · ${permanent} permanent`;
    document.getElementById('oc-add-template-btn').href =
      `/offer-templates/create?account=${accountId}`;

    document.getElementById('oc-search').value      = '';
    document.getElementById('oc-perm-filter').value = '';
    clearOcBulkSelection();

    const oc = new bootstrap.Offcanvas(document.getElementById('templatesOffcanvas'));
    oc.show();
    loadTemplates();
  }

  document.querySelectorAll('.btn-manage-templates').forEach(btn => {
    btn.addEventListener('click', function () {
      openTemplateOffcanvas(
        this.dataset.accountId,
        this.dataset.accountEmail,
        this.dataset.total,
        this.dataset.permanent
      );
    });
  });

  // ── Search / filter inside offcanvas ─────────────────────────────────────────
  document.getElementById('oc-search').addEventListener('input', function () {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => { currentPage = 1; loadTemplates(); }, 380);
  });

  document.getElementById('oc-perm-filter').addEventListener('change', function () {
    currentPage = 1;
    loadTemplates();
  });

  // ── Load templates via AJAX ───────────────────────────────────────────────────
  function loadTemplates() {
    if (!currentAccountId) return;

    const search    = document.getElementById('oc-search').value.trim();
    const permanent = document.getElementById('oc-perm-filter').value;
    const params    = new URLSearchParams({ page: currentPage });
    if (search)    params.set('search', search);
    if (permanent) params.set('permanent', permanent);

    showOcState('loading');

    fetch(`/automation/user/${currentAccountId}/templates?${params}`, {
      headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    })
    .then(r => r.json())
    .then(data => {
      lastMeta = data;
      renderTemplates(data.data ?? []);
      renderPagination(data);
    })
    .catch(() => showOcState('empty'));
  }

  // ── Render template rows ──────────────────────────────────────────────────────
  function renderTemplates(templates) {
    if (!templates.length) { showOcState('empty'); return; }

    showOcState('table');
    const tbody = document.getElementById('oc-tbody');
    tbody.innerHTML = templates.map(t => {
      const lastPosted = t.last_posted_at
        ? `<span title="${t.last_posted_at}">${relativeTime(t.last_posted_at)}</span>`
        : '<span class="text-muted">—</span>';

      const levels = [
        t.th_level    ? `TH${t.th_level}`    : null,
        t.king_level  ? `K${t.king_level}`   : null,
        t.queen_level ? `Q${t.queen_level}`  : null,
      ].filter(Boolean).join(' · ');

      const queueBadge = (t.offers_to_generate ?? 0) > 0
        ? `<span class="badge bg-warning text-dark" id="oc-queue-${t.id}" title="Queued posts">
             <i class="ri-send-plane-line me-1"></i>${t.offers_to_generate}
           </span>`
        : `<span class="badge bg-secondary-subtle text-secondary" id="oc-queue-${t.id}">—</span>`;

      const permBtn = t.is_permanent
        ? `<button class="btn btn-sm btn-success oc-perm-btn" data-id="${t.id}" data-permanent="1" title="Protected — click to unmark">
             <i class="ri-shield-check-line"></i>
           </button>`
        : `<button class="btn btn-sm btn-outline-secondary oc-perm-btn" data-id="${t.id}" data-permanent="0" title="Deletable — click to protect">
             <i class="ri-shield-line"></i>
           </button>`;

      return `
        <tr id="oc-row-${t.id}">
          <td class="ps-3">
            <div class="form-check mb-0">
              <input class="form-check-input oc-cb" type="checkbox" value="${t.id}">
            </div>
          </td>
          <td>
            <div class="fw-medium lh-sm" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${escHtml(t.title)}">
              ${escHtml(t.title)}
            </div>
            <div class="text-muted small">${levels}${t.price ? ` · $${parseFloat(t.price).toFixed(2)}` : ''}</div>
          </td>
          <td class="text-center">${permBtn}</td>
          <td class="text-center">${queueBadge}</td>
          <td class="text-muted small d-none d-md-table-cell">${lastPosted}</td>
          <td class="text-end pe-3">
            <button class="btn btn-sm btn-outline-warning oc-queue-btn" data-id="${t.id}" title="Queue +1 forced post">
              <i class="ri-add-circle-line"></i>
            </button>
            <a href="/offer-templates/${t.id}/edit" class="btn btn-sm btn-outline-secondary ms-1" title="Edit">
              <i class="ri-edit-line"></i>
            </a>
            <button class="btn btn-sm btn-outline-danger ms-1 oc-delete-btn" data-id="${t.id}" data-title="${escHtml(t.title)}" title="Delete from DB">
              <i class="ri-delete-bin-line"></i>
            </button>
          </td>
        </tr>`;
    }).join('');

    bindOcRowActions();
    updateOcSelectAll();
  }

  // ── Bind row-level actions ────────────────────────────────────────────────────
  function bindOcRowActions() {
    document.querySelectorAll('.oc-cb').forEach(cb => {
      cb.addEventListener('change', updateOcBulkBar);
    });

    document.getElementById('oc-select-all').addEventListener('change', function () {
      document.querySelectorAll('.oc-cb').forEach(c => c.checked = this.checked);
      updateOcBulkBar();
    });

    document.querySelectorAll('.oc-perm-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const id          = this.dataset.id;
        const isPermanent = this.dataset.permanent === '1';

        Swal.fire({
          title: isPermanent ? 'Remove protection?' : 'Mark as permanent?',
          text:  isPermanent
            ? 'This offer will be included in future delete-all runs.'
            : 'This offer will be skipped during delete-all runs.',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes',
          cancelButtonText: 'Cancel',
          customClass: { confirmButton: 'btn btn-primary w-xs me-2 mt-2', cancelButton: 'btn btn-outline-secondary w-xs mt-2' },
          buttonsStyling: false,
          showCloseButton: true,
        }).then(result => {
          if (!result.isConfirmed) return;
          fetch(`/offer-templates/${id}/toggle-permanent`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(data => {
            if (!data.success) return;
            const p   = data.is_permanent;
            this.dataset.permanent = p ? '1' : '0';
            this.className = p ? 'btn btn-sm btn-success oc-perm-btn' : 'btn btn-sm btn-outline-secondary oc-perm-btn';
            this.innerHTML = p ? '<i class="ri-shield-check-line"></i>' : '<i class="ri-shield-line"></i>';
            this.title     = p ? 'Protected — click to unmark' : 'Deletable — click to protect';
          });
        });
      });
    });

    document.querySelectorAll('.oc-queue-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.dataset.id;
        this.disabled = true;
        fetch(`/offer-templates/${id}/queue-post`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body: JSON.stringify({ user_account_id: currentAccountId }),
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const badge = document.getElementById(`oc-queue-${id}`);
            if (badge) {
              badge.className = 'badge bg-warning text-dark';
              badge.innerHTML = `<i class="ri-send-plane-line me-1"></i>${data.offers_to_generate}`;
            }
            Swal.fire({ title: 'Queued!', text: `Post queue: ${data.offers_to_generate}`, icon: 'success', timer: 1000, showConfirmButton: false });
          }
        })
        .finally(() => { this.disabled = false; });
      });
    });

    document.querySelectorAll('.oc-delete-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const title = this.dataset.title;
        Swal.fire({
          title: 'Delete from database?',
          html: `<strong>${title}</strong><br><span class="text-muted small">Removes the record only — does not touch g2g.com.</span>`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete',
          cancelButtonText: 'Cancel',
          customClass: { confirmButton: 'btn btn-danger w-xs me-2 mt-2', cancelButton: 'btn btn-secondary w-xs mt-2' },
          buttonsStyling: false,
          showCloseButton: true,
        }).then(result => {
          if (!result.isConfirmed) return;
          fetch(`/offer-templates/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          })
          .then(r => r.json())
          .then(data => {
            if (data.success) {
              document.getElementById(`oc-row-${id}`)?.remove();
              Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1200, showConfirmButton: false });
            }
          });
        });
      });
    });
  }

  // ── Bulk selection helpers ────────────────────────────────────────────────────
  function getOcChecked() {
    return Array.from(document.querySelectorAll('.oc-cb:checked'));
  }

  function updateOcBulkBar() {
    const checked = getOcChecked();
    const bar     = document.getElementById('oc-bulk-bar');
    const label   = document.getElementById('oc-bulk-count');
    bar.classList.toggle('d-none', checked.length === 0);
    label.textContent = `${checked.length} selected`;
    updateOcSelectAll();
  }

  function updateOcSelectAll() {
    const all     = document.querySelectorAll('.oc-cb');
    const checked = getOcChecked();
    const sa      = document.getElementById('oc-select-all');
    if (!sa) return;
    sa.indeterminate = checked.length > 0 && checked.length < all.length;
    sa.checked       = all.length > 0 && checked.length === all.length;
  }

  function clearOcBulkSelection() {
    document.querySelectorAll('.oc-cb').forEach(c => c.checked = false);
    document.getElementById('oc-select-all').checked = false;
    document.getElementById('oc-bulk-bar').classList.add('d-none');
  }

  document.getElementById('oc-bulk-clear').addEventListener('click', clearOcBulkSelection);

  // ── Bulk actions ──────────────────────────────────────────────────────────────
  document.querySelectorAll('.oc-bulk-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const action  = this.dataset.action;
      const checked = getOcChecked();
      const ids     = checked.map(c => parseInt(c.value, 10));
      if (!ids.length) return;

      const labels = {
        mark_permanent:   `Mark ${ids.length} template(s) as permanent?`,
        unmark_permanent: `Unmark ${ids.length} template(s)?`,
        queue_post:       `Add +1 post queue to ${ids.length} template(s)?`,
        queue_dequeue:    `Subtract -1 post queue from ${ids.length} template(s)?`,
        delete:           `Permanently delete ${ids.length} template(s) from the database?`,
      };

      Swal.fire({
        title: labels[action],
        text: action === 'delete'
          ? 'This cannot be undone.'
          : action === 'mark_permanent' ? 'These offers will be skipped during delete-all runs.' : '',
        icon: action === 'delete' ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        customClass: {
          confirmButton: action === 'delete' ? 'btn btn-danger w-xs me-2 mt-2' : 'btn btn-primary w-xs me-2 mt-2',
          cancelButton: 'btn btn-secondary w-xs mt-2',
        },
        buttonsStyling: false,
        showCloseButton: true,
      }).then(result => {
        if (!result.isConfirmed) return;
        fetch('/offer-templates/bulk-action', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
          body: JSON.stringify({ action, ids, user_account_id: currentAccountId }),
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            if (action === 'delete') {
              ids.forEach(id => document.getElementById(`oc-row-${id}`)?.remove());
              clearOcBulkSelection();
              Swal.fire({ title: 'Done!', text: `${data.count} template(s) deleted.`, icon: 'success', timer: 1500, showConfirmButton: false });
            } else {
              Swal.fire({ title: 'Done!', text: `${data.count} template(s) updated.`, icon: 'success', timer: 1500, showConfirmButton: false })
                .then(() => { currentPage = 1; loadTemplates(); });
            }
          }
        })
        .catch(() => Swal.fire({ title: 'Error', text: 'Action failed.', icon: 'error' }));
      });
    });
  });

  // ── Pagination ────────────────────────────────────────────────────────────────
  function renderPagination(data) {
    const wrap = document.getElementById('oc-pagination');
    const info = document.getElementById('oc-page-info');
    const prev = document.getElementById('oc-prev');
    const next = document.getElementById('oc-next');

    if ((data.last_page ?? 1) <= 1) { wrap.classList.add('d-none'); return; }

    wrap.classList.remove('d-none');
    info.textContent = `Showing ${data.from}–${data.to} of ${data.total}`;
    prev.disabled = data.current_page <= 1;
    next.disabled = data.current_page >= data.last_page;
  }

  document.getElementById('oc-prev').addEventListener('click', function () {
    if (currentPage > 1) { currentPage--; loadTemplates(); }
  });
  document.getElementById('oc-next').addEventListener('click', function () {
    if (currentPage < (lastMeta.last_page ?? 1)) { currentPage++; loadTemplates(); }
  });

  // ── Delete All from g2g (per-row button) ─────────────────────────────────────
  document.querySelectorAll('.btn-delete-all-g2g').forEach(btn => {
    btn.addEventListener('click', function () {
      const id           = this.dataset.accountId;
      const email        = this.dataset.accountEmail;
      const permanentCnt = parseInt(this.dataset.permanentCount, 10);
      const queued       = this.dataset.queued === '1';
      const btnEl        = this;

      if (queued) {
        Swal.fire({
          title: 'Cancel delete-all?',
          text: `The desktop app will no longer wipe offers for ${email}.`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, cancel it',
          cancelButtonText: 'Keep queued',
          customClass: { confirmButton: 'btn btn-secondary w-xs me-2 mt-2', cancelButton: 'btn btn-outline-secondary w-xs mt-2' },
          buttonsStyling: false,
        }).then(result => { if (result.isConfirmed) doToggleDeleteAll(id, email, false, permanentCnt, btnEl); });
      } else {
        openQaModal('delete-except-permanent');
        const accountCard = document.querySelector(`.qa-account-card[data-id="${id}"]`);
        if (accountCard) {
          accountCard.click();
          qaNext.click();
          qaBack.classList.add('d-none');
        }
      }
    });
  });

  function doToggleDeleteAll(accountId, email, queueing, permanentCnt, btnEl) {
    fetch(`/user-accounts/${accountId}/queue-delete-all`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
      if (!data.success) return;
      const isQueued = data.queue_delete_all;
      btnEl.dataset.queued = isQueued ? '1' : '0';
      btnEl.className = btnEl.className.replace(/btn-(warning|outline-warning)/, isQueued ? 'btn-warning' : 'btn-outline-warning');
      btnEl.title = isQueued ? 'Cancel delete-all' : 'Queue: delete all g2g offers (skips permanent)';

      const row        = document.getElementById(`account-row-${accountId}`);
      const statusCell = row?.querySelector('td:nth-child(5)');
      if (statusCell) {
        statusCell.innerHTML = isQueued
          ? `<span class="badge bg-danger"><i class="ri-delete-bin-line me-1"></i>Queued</span>`
          : `<span class="text-muted small">—</span>`;
      }

      Swal.fire({
        title: isQueued ? 'Delete-all queued!' : 'Cancelled.',
        text: isQueued
          ? `The desktop app will delete all${permanentCnt > 0 ? ` non-permanent` : ''} offers for ${email} on next run.`
          : 'Delete-all has been cancelled.',
        icon: 'success',
        timer: 2500,
        showConfirmButton: false,
      });
    })
    .catch(() => Swal.fire({ title: 'Error', text: 'Could not update.', icon: 'error' }));
  }

  // ── Delete Account ────────────────────────────────────────────────────────────
  document.querySelectorAll('.btn-delete-account').forEach(btn => {
    btn.addEventListener('click', function () {
      const id        = this.dataset.accountId;
      const email     = this.dataset.accountEmail;
      const templates = parseInt(this.dataset.templates, 10);
      const warning   = templates > 0
        ? `This will also permanently delete <strong>${templates} template${templates !== 1 ? 's' : ''}</strong> linked to this account.`
        : 'This action cannot be undone.';

      Swal.fire({
        title: 'Delete account?',
        html: `<strong>${email}</strong><br><span class="text-muted small">${warning}</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
        customClass: { confirmButton: 'btn btn-danger w-xs me-2 mt-2', cancelButton: 'btn btn-secondary w-xs mt-2' },
        buttonsStyling: false,
        showCloseButton: true,
      }).then(result => {
        if (!result.isConfirmed) return;
        fetch(`/user-accounts/${id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            document.getElementById(`account-row-${id}`)?.remove();
            Swal.fire({ title: 'Deleted!', icon: 'success', timer: 1200, showConfirmButton: false });
          }
        })
        .catch(() => Swal.fire({ title: 'Error', text: 'Could not delete account.', icon: 'error' }));
      });
    });
  });

  // ══════════════════════════════════════════════════════════════════════════════
  // Quick Action Modal
  // ══════════════════════════════════════════════════════════════════════════════

  const QA_CONFIGS = {
    'manage-templates': {
      icon:        'ri-layout-grid-line',
      iconBg:      'bg-primary-subtle text-primary',
      title:       'Template Management',
      subtitle:    'Select an account to open its templates',
      confirmCls:  'btn-primary',
      confirmHtml: '<i class="ri-layout-grid-line me-1"></i>Open Templates',
      warning:     null,
      step2Alert:  null,
    },
    'queue-post-all': {
      icon:        'ri-send-plane-line',
      iconBg:      'bg-success-subtle text-success',
      title:       'Post for Account',
      subtitle:    'Select an account, then choose which game to post',
      confirmCls:  'btn-success',
      confirmHtml: '<i class="ri-send-plane-line me-1"></i>Queue Post',
      warning:     null,
      step2Alert: {
        cls:  'alert-info border-info-subtle',
        icon: 'ri-information-line text-info',
        html: 'Templates for the selected game will be queued for posting on the next desktop app run.',
      },
      gameDataAttr: 'allGames',
      gameSubtitle: (count) => `${count} template${count !== 1 ? 's' : ''}`,
    },
    'delete-except-permanent': {
      icon:        'ri-shield-line',
      iconBg:      'bg-warning-subtle text-warning',
      title:       'Delete All Except Permanent',
      subtitle:    'Select an account, then choose which game to target',
      confirmCls:  'btn-danger',
      confirmHtml: '<i class="ri-delete-bin-line me-1"></i>Queue Delete',
      warning:     null,
      step2Alert: {
        cls:  'alert-warning border-warning-subtle',
        icon: 'ri-alert-line text-warning',
        html: 'All <strong>non-permanent</strong> live offers for the selected game will be deleted from g2g.com on the next desktop app run. <strong>Permanent offers are protected.</strong>',
      },
      gameDataAttr: 'games',
      gameSubtitle: (count) => `${count} non-permanent template${count !== 1 ? 's' : ''}`,
    },
  };

  let qaAction       = null;
  let qaAccountId    = null;
  let qaEmail        = null;
  let qaSelectedGame = null;
  let qaGameLabel    = null;
  const QA_TWO_STEP  = new Set(['delete-except-permanent', 'queue-post-all']);
  const qaModalEl    = document.getElementById('quickActionModal');
  const qaModal      = new bootstrap.Modal(qaModalEl);
  const qaConfirm    = document.getElementById('qa-confirm');
  const qaNext       = document.getElementById('qa-next');
  const qaBack       = document.getElementById('qa-back');

  function openQaModal(action) {
    qaAction       = action;
    qaAccountId    = null;
    qaEmail        = null;
    qaSelectedGame = null;
    qaGameLabel    = null;

    const cfg      = QA_CONFIGS[action];
    const twoStep  = QA_TWO_STEP.has(action);

    document.getElementById('qa-icon').className        = `${cfg.icon} fs-18`;
    document.getElementById('qa-icon-wrap').className   = `avatar-title rounded-circle ${cfg.iconBg}`;
    document.getElementById('qaModalTitle').textContent = cfg.title;
    document.getElementById('qa-subtitle').textContent  = cfg.subtitle;
    qaConfirm.innerHTML  = cfg.confirmHtml;
    qaConfirm.className  = `btn ${cfg.confirmCls}`;
    qaConfirm.disabled   = true;

    const warnEl = document.getElementById('qa-warning');
    if (cfg.warning) {
      warnEl.className = `alert ${cfg.warning.cls} d-flex align-items-start gap-2 mb-3`;
      document.getElementById('qa-warn-icon').className = cfg.warning.icon;
      document.getElementById('qa-warn-text').innerHTML = cfg.warning.html;
      warnEl.classList.remove('d-none');
    } else {
      warnEl.classList.add('d-none');
    }

    document.getElementById('qa-step-bar').classList.toggle('d-none', !twoStep);
    document.getElementById('qa-step-1-body').classList.remove('d-none');
    document.getElementById('qa-step-2-body').classList.add('d-none');
    qaNext.classList.toggle('d-none', !twoStep);
    qaNext.disabled = true;
    qaBack.classList.add('d-none');
    qaConfirm.classList.toggle('d-none', twoStep);

    if (twoStep) updateStepIndicator(1);

    document.querySelectorAll('.qa-account-card').forEach(card => {
      card.classList.remove('border-primary', 'bg-primary-subtle');
      card.querySelector('.qa-radio').checked = false;
    });

    qaModal.show();
  }

  function updateStepIndicator(step) {
    const pill1 = document.getElementById('qa-pill-1');
    const lbl1  = document.getElementById('qa-step-lbl-1');
    const pill2 = document.getElementById('qa-pill-2');
    const lbl2  = document.getElementById('qa-step-lbl-2');
    if (step === 1) {
      pill1.className   = 'badge rounded-pill bg-primary';
      pill1.innerHTML   = '1';
      lbl1.className    = 'fw-medium';
      pill2.className   = 'badge rounded-pill border text-muted';
      pill2.textContent = '2';
      lbl2.className    = 'text-muted';
    } else {
      pill1.className = 'badge rounded-pill bg-success';
      pill1.innerHTML = '<i class="ri-check-line"></i>';
      lbl1.className  = 'text-muted';
      pill2.className   = 'badge rounded-pill bg-primary';
      pill2.textContent = '2';
      lbl2.className    = 'fw-medium';
    }
  }

  function populateGameList() {
    const card       = document.querySelector(`.qa-account-card[data-id="${qaAccountId}"]`);
    const cfg        = QA_CONFIGS[qaAction];
    const attrKey    = cfg.gameDataAttr ?? 'games';
    const subtitleFn = cfg.gameSubtitle ?? ((n) => `${n} template${n !== 1 ? 's' : ''}`);

    // Convert camelCase data attr key to dataset key (e.g. 'allGames' → card.dataset.allGames)
    const games = JSON.parse(card?.dataset[attrKey] || '[]');
    const total = games.reduce((s, g) => s + g.count, 0);

    const gameMeta = {
      clash_of_clans:      { icon: 'ri-shield-line',   cls: 'bg-primary' },
      brawl_stars:         { icon: 'ri-star-line',     cls: 'bg-warning' },
      clash_royale:        { icon: 'ri-medal-line',    cls: 'bg-info' },
      hay_day:             { icon: 'ri-seedling-line', cls: 'bg-success' },
      mobile_legends:      { icon: 'ri-sword-line',    cls: 'bg-danger' },
      call_of_duty_mobile: { icon: 'ri-crosshair-2-line', cls: 'bg-dark' },
    };

    function gameCard(game, label, count, subtitle) {
      const meta = gameMeta[game] || { icon: 'ri-gamepad-line', cls: 'bg-secondary' };
      const colorCls = game ? meta.cls : 'bg-secondary';
      const iconCls  = game ? meta.icon : 'ri-apps-2-line';
      return `
        <div class="qa-game-card d-flex align-items-center gap-3 p-2 px-3 border rounded-2" role="button"
             data-game="${escHtml(game)}" data-label="${escHtml(label)}">
          <div class="avatar-title rounded-circle ${colorCls} text-white flex-shrink-0"
               style="width:32px;height:32px;font-size:16px">
            <i class="${iconCls}"></i>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold small">${escHtml(label)}</div>
            <div class="text-muted" style="font-size:.75rem">${subtitle}</div>
          </div>
          <span class="badge bg-secondary-subtle text-secondary flex-shrink-0">${count}</span>
          <div class="form-check mb-0 flex-shrink-0">
            <input class="form-check-input qa-game-radio" type="radio" name="qa-game-pick" style="pointer-events:none">
          </div>
        </div>`;
    }

    const allSubtitle = `${subtitleFn(total)} across all games`;
    const rows = [
      gameCard('', 'All Games', total, allSubtitle),
      ...games.map(g => gameCard(g.game, g.label, g.count, subtitleFn(g.count))),
    ];

    const list = document.getElementById('qa-game-list');
    list.innerHTML = rows.join('');

    list.querySelectorAll('.qa-game-card').forEach(c => {
      c.addEventListener('click', () => {
        list.querySelectorAll('.qa-game-card').forEach(x => {
          x.classList.remove('border-primary', 'bg-primary-subtle');
          x.querySelector('.qa-game-radio').checked = false;
        });
        c.classList.add('border-primary', 'bg-primary-subtle');
        c.querySelector('.qa-game-radio').checked = true;
        qaSelectedGame = c.dataset.game || null;
        qaGameLabel    = c.dataset.label;
        qaConfirm.disabled = false;
      });
    });
  }

  // Account card selection
  document.querySelectorAll('.qa-account-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.qa-account-card').forEach(c => {
        c.classList.remove('border-primary', 'bg-primary-subtle');
        c.querySelector('.qa-radio').checked = false;
      });
      card.classList.add('border-primary', 'bg-primary-subtle');
      card.querySelector('.qa-radio').checked = true;
      qaAccountId = card.dataset.id;
      qaEmail     = card.dataset.email;
      if (QA_TWO_STEP.has(qaAction)) {
        qaNext.disabled = false;
      } else {
        qaConfirm.disabled = false;
      }
    });
  });

  // Next button: advance to step 2
  qaNext.addEventListener('click', () => {
    if (!qaAccountId) return;
    updateStepIndicator(2);
    document.getElementById('qa-step-1-body').classList.add('d-none');
    document.getElementById('qa-step-2-body').classList.remove('d-none');
    qaNext.classList.add('d-none');
    qaBack.classList.remove('d-none');
    qaConfirm.classList.remove('d-none');
    qaConfirm.disabled = true;
    qaSelectedGame = null;
    qaGameLabel    = null;

    const step2Alert = QA_CONFIGS[qaAction]?.step2Alert;
    const alertEl    = document.getElementById('qa-step-2-alert');
    if (step2Alert) {
      alertEl.className = `alert d-flex align-items-start gap-2 mb-3 ${step2Alert.cls}`;
      document.getElementById('qa-step-2-alert-icon').className = `${step2Alert.icon} mt-1 flex-shrink-0`;
      document.getElementById('qa-step-2-alert-text').innerHTML  = step2Alert.html;
    } else {
      alertEl.classList.add('d-none');
    }

    document.getElementById('qa-game-list').querySelectorAll('.qa-game-card').forEach(c => {
      c.classList.remove('border-primary', 'bg-primary-subtle');
    });
    populateGameList();
  });

  // Back button: return to step 1
  qaBack.addEventListener('click', () => {
    updateStepIndicator(1);
    document.getElementById('qa-step-1-body').classList.remove('d-none');
    document.getElementById('qa-step-2-body').classList.add('d-none');
    qaBack.classList.add('d-none');
    qaNext.classList.remove('d-none');
    qaNext.disabled = !qaAccountId;
    qaConfirm.classList.add('d-none');
    qaSelectedGame = null;
    qaGameLabel    = null;
  });

  // Quick action buttons → open modal
  document.querySelectorAll('.qa-btn').forEach(btn => {
    btn.addEventListener('click', () => openQaModal(btn.dataset.action));
  });

  // ── Post All Accounts ─────────────────────────────────────────────────────────
  document.getElementById('qa-post-all-btn')?.addEventListener('click', () => {
    Swal.fire({
      title: 'Post All Accounts?',
      html: 'This will queue <strong>all templates across every account</strong> to be posted on the next desktop app cycle. Are you sure?',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i class="ri-play-circle-line me-1"></i>Post All Now',
      cancelButtonText: 'Cancel',
      customClass: {
        confirmButton: 'btn btn-primary w-xs me-2 mt-2',
        cancelButton:  'btn btn-secondary w-xs mt-2',
      },
      buttonsStyling: false,
      showCloseButton: true,
    }).then(async result => {
      if (!result.isConfirmed) return;

      try {
        const res  = await fetch('/offer-templates/queue-post-all-accounts', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.success) {
          Swal.fire({
            title: 'All Queued!',
            html:  `<strong>${data.queued}</strong> template(s) queued across all accounts. The desktop app will post them on the next cycle.`,
            icon:  'success',
            timer: 3000,
            showConfirmButton: false,
          });
        } else {
          Swal.fire({ title: 'Error', text: data.message ?? 'Action failed.', icon: 'error' });
        }
      } catch {
        Swal.fire({ title: 'Error', text: 'Request failed. Please try again.', icon: 'error' });
      }
    });
  });

  // Confirm handler
  qaConfirm.addEventListener('click', async () => {
    if (!qaAccountId) return;

    if (qaAction === 'manage-templates') {
      qaModal.hide();
      const card = document.querySelector(`.qa-account-card[data-id="${qaAccountId}"]`);
      openTemplateOffcanvas(
        qaAccountId,
        qaEmail,
        card?.dataset.total ?? 0,
        card?.dataset.permanent ?? 0
      );
      return;
    }

    const ENDPOINTS = {
      'queue-post-all':          { url: '/offer-templates/queue-post-by-account', body: Object.assign({ user_account_id: qaAccountId }, qaSelectedGame ? { game: qaSelectedGame } : {}) },
      'delete-except-permanent': { url: `/user-accounts/${qaAccountId}/queue-delete-non-permanent`, body: qaSelectedGame ? { game: qaSelectedGame } : {} },
    };

    const ep = ENDPOINTS[qaAction];
    if (!ep) return;

    qaConfirm.disabled = true;
    qaConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing…';

    try {
      const res  = await fetch(ep.url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify(ep.body ?? {}),
      });
      const data = await res.json();

      qaModal.hide();

      if (data.success) {
        const SUCCESS_MSGS = {
          'queue-post-all': {
            title: 'Posts Queued!',
            text:  qaSelectedGame
              ? `<strong>${escHtml(qaGameLabel)}</strong> templates for <strong>${escHtml(qaEmail)}</strong> have been queued. The desktop app will process them on the next cycle.`
              : `All templates for <strong>${escHtml(qaEmail)}</strong> have been queued. The desktop app will process them on the next cycle.`,
          },
          'delete-except-permanent': {
            title: 'Deletion Queued!',
            text:  qaSelectedGame
              ? `Non-permanent <strong>${escHtml(qaGameLabel)}</strong> offers for <strong>${escHtml(qaEmail)}</strong> will be deleted on the next desktop app run.`
              : `All non-permanent offers for <strong>${escHtml(qaEmail)}</strong> will be deleted on the next desktop app run.`,
          },
        };
        const msg = SUCCESS_MSGS[qaAction];
        Swal.fire({ title: msg.title, html: msg.text, icon: 'success', timer: 3000, showConfirmButton: false });

        if (qaAction === 'delete-except-permanent') {
          const row        = document.getElementById(`account-row-${qaAccountId}`);
          const statusCell = row?.querySelector('td:nth-child(5)');
          if (statusCell) {
            const gameTag = qaSelectedGame ? ` · ${escHtml(qaGameLabel)}` : '';
            statusCell.innerHTML = `<span class="badge bg-danger"><i class="ri-delete-bin-line me-1"></i>Queued${gameTag}</span>`;
          }
          const deleteBtn = row?.querySelector('.btn-delete-all-g2g');
          if (deleteBtn) {
            deleteBtn.dataset.queued = '1';
            deleteBtn.className = deleteBtn.className.replace(/btn-(warning|outline-warning)/, 'btn-warning');
            deleteBtn.title = 'Cancel delete-all';
          }
        }
      } else {
        Swal.fire({ title: 'Error', text: data.message ?? 'Action failed.', icon: 'error' });
      }
    } catch (err) {
      qaModal.hide();
      Swal.fire({ title: 'Error', text: 'Request failed. Please try again.', icon: 'error' });
    }
  });

  // Reset modal state when it closes
  qaModalEl.addEventListener('hidden.bs.modal', () => {
    const cfg = qaAction ? QA_CONFIGS[qaAction] : null;
    if (cfg) {
      qaConfirm.innerHTML = cfg.confirmHtml;
      qaConfirm.className = `btn ${cfg.confirmCls}`;
      qaConfirm.disabled  = true;
    }
    document.getElementById('qa-step-bar').classList.add('d-none');
    document.getElementById('qa-step-1-body').classList.remove('d-none');
    document.getElementById('qa-step-2-body').classList.add('d-none');
    qaNext.classList.add('d-none');
    qaNext.disabled = true;
    qaBack.classList.add('d-none');
    qaConfirm.classList.remove('d-none');
    qaSelectedGame = null;
    qaGameLabel    = null;
  });

  // ── Helpers ───────────────────────────────────────────────────────────────────
  function showOcState(state) {
    document.getElementById('oc-loading').classList.toggle('d-none',    state !== 'loading');
    document.getElementById('oc-empty').classList.toggle('d-none',      state !== 'empty');
    document.getElementById('oc-table-wrap').classList.toggle('d-none', state !== 'table');
    if (state !== 'table') document.getElementById('oc-pagination').classList.add('d-none');
  }

  function escHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function relativeTime(isoStr) {
    const diff = Math.floor((Date.now() - new Date(isoStr)) / 1000);
    if (diff < 60)    return `${diff}s ago`;
    if (diff < 3600)  return `${Math.floor(diff/60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
    return `${Math.floor(diff/86400)}d ago`;
  }
</script>
@endpush
