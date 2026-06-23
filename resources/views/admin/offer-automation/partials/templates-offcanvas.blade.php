<div class="offcanvas offcanvas-end" tabindex="-1" id="templatesOffcanvas"
     style="width:min(760px,100vw)" aria-labelledby="templatesOffcanvasLabel">

  <div class="offcanvas-header border-bottom pb-3">
    <div>
      <h5 class="offcanvas-title mb-0" id="templatesOffcanvasLabel">Templates</h5>
      <div class="text-muted small mt-1" id="oc-account-meta"></div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <div class="px-3 py-2 border-bottom bg-light d-flex align-items-center gap-2 flex-wrap">
    <div class="input-group input-group-sm flex-grow-1" style="min-width:180px;max-width:300px">
      <span class="input-group-text bg-white"><i class="ri-search-line text-muted"></i></span>
      <input type="text" id="oc-search" class="form-control border-start-0 ps-0" placeholder="Search templates…">
    </div>
    <select id="oc-perm-filter" class="form-select form-select-sm" style="max-width:160px">
      <option value="">All templates</option>
      <option value="permanent">Permanent only</option>
      <option value="non_permanent">Non-permanent</option>
    </select>
    <a id="oc-add-template-btn" href="#" class="btn btn-sm btn-primary ms-auto">
      <i class="ri-add-line me-1"></i>Add Template
    </a>
  </div>

  {{-- Bulk action bar (hidden until rows selected) --}}
  <div id="oc-bulk-bar" class="d-none px-3 py-2 bg-dark text-white border-bottom">
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <span class="fw-semibold small" id="oc-bulk-count">0 selected</span>
      <div class="vr mx-1"></div>
      <button type="button" class="btn btn-sm btn-success oc-bulk-btn" data-action="mark_permanent">
        <i class="ri-shield-check-line me-1"></i>Mark Permanent
      </button>
      <button type="button" class="btn btn-sm btn-outline-light oc-bulk-btn" data-action="unmark_permanent">
        <i class="ri-shield-line me-1"></i>Unmark
      </button>
      <button type="button" class="btn btn-sm btn-info text-dark oc-bulk-btn" data-action="queue_post">
        <i class="ri-add-circle-line me-1"></i>Queue +1
      </button>
      <button type="button" class="btn btn-sm btn-outline-info text-light border-info oc-bulk-btn" data-action="queue_dequeue">
        <i class="ri-indeterminate-circle-line me-1"></i>Queue -1
      </button>
      <button type="button" class="btn btn-sm btn-outline-danger oc-bulk-btn" data-action="delete">
        <i class="ri-delete-bin-line me-1"></i>Delete
      </button>
      <button type="button" class="btn btn-sm btn-outline-light ms-auto" id="oc-bulk-clear">
        <i class="ri-close-line"></i>
      </button>
    </div>
  </div>

  <div class="offcanvas-body p-0">
    <div id="oc-loading" class="py-5 text-center text-muted d-none">
      <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
      <p class="small mb-0">Loading templates…</p>
    </div>
    <div id="oc-empty" class="py-5 text-center text-muted d-none">
      <i class="ri-file-list-3-line display-3 d-block mb-2 opacity-25"></i>
      <p class="mb-0">No templates match your filter.</p>
    </div>
    <div class="table-responsive d-none" id="oc-table-wrap">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead class="table-light sticky-top">
          <tr>
            <th style="width:36px" class="ps-3">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="oc-select-all">
              </div>
            </th>
            <th>Template</th>
            <th class="text-center" style="width:95px">Permanent</th>
            <th class="text-center" style="width:70px">Queue</th>
            <th class="d-none d-md-table-cell" style="width:110px">Last Posted</th>
            <th class="text-end pe-3" style="width:110px">Actions</th>
          </tr>
        </thead>
        <tbody id="oc-tbody"></tbody>
      </table>
    </div>
    <div id="oc-pagination" class="d-none px-3 py-3 border-top d-flex align-items-center justify-content-between gap-2 flex-wrap">
      <div class="text-muted small" id="oc-page-info"></div>
      <div class="d-flex gap-1">
        <button class="btn btn-sm btn-outline-secondary" id="oc-prev" disabled>
          <i class="ri-arrow-left-s-line"></i> Prev
        </button>
        <button class="btn btn-sm btn-outline-secondary" id="oc-next" disabled>
          Next <i class="ri-arrow-right-s-line"></i>
        </button>
      </div>
    </div>
  </div>
</div>
