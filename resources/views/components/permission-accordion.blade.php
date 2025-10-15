@props(['permissionGroups', 'role' => []])
{{-- @dd($role->permissions->groupBy('group')); --}}
<div id="permissionsAccordion" class="accordion accordion-flush">
    @foreach ($permissionGroups as $key => $group)
        @php
            $groupId = str($key)->camel();
        @endphp
        <div class="accordion-item material-shadow">
            <h2 class="accordion-header" id="heading-{{ $groupId }}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapse-{{ $groupId }}" aria-expanded="false"
                    aria-controls="collapse-{{ $groupId }}">
                    {{ $key }}
                </button>
            </h2>
            <div id="collapse-{{ $groupId }}" class="accordion-collapse collapse"
                aria-labelledby="heading-{{ $groupId }}" data-bs-parent="#permissionsAccordion">
                <div class="accordion-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input select-all-permissions" type="checkbox"
                            id="selectAll-{{ $groupId }}" data-group="{{ $groupId }}"
                            {{ !empty($role) && $group->every(fn($permission) => $role->permissions->contains('id', $permission['id'])) ? 'checked' : '' }} />
                        <label class="form-check-label fw-bold" for="selectAll-{{ $groupId }}">
                            Select All {{ $key }}
                        </label>
                    </div>
                    <div id="permissions-{{ $groupId }}" class="d-flex justify-content-between">
                        @foreach ($group as $permission)
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]"
                                    value="{{ $permission['id'] }}" data-group="{{ $groupId }}"
                                    id="{{ $key }}-{{ $permission['name'] }}"
                                    @if (!empty($role) && $role->permissions->contains('id', $permission['id'])) checked @endif>
                                <label class="form-check-label" for="{{ $key }}-{{ $permission['name'] }}">
                                    {{ $permission['name'] }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Update "Select All" checkbox when individual checkboxes are toggled
            $('.permission-checkbox').on('change', function() {
                var groupId = $(this).data('group'); // Group identifier from data-group attribute
                var allChecked = true;

                // Check if all checkboxes in the group are selected
                $('.permission-checkbox[data-group="' + groupId + '"]').each(function() {
                    if (!$(this).is(':checked')) {
                        allChecked = false; // Found an unchecked box
                        return false; // Exit loop
                    }
                });

                // Update "Select All" checkbox for the group
                $('#selectAll-' + groupId).prop('checked', allChecked);
            });

            // Select/Deselect all checkboxes in a group when "Select All" is toggled
            $('.select-all-permissions').on('change', function() {
                var groupId = $(this).data('group'); // Group identifier from data-group attribute
                var isChecked = $(this).is(':checked');

                // Update all individual checkboxes in the group
                $('.permission-checkbox[data-group="' + groupId + '"]').prop('checked', isChecked);
            });

            // Initialize "Select All" checkboxes on page load
            $('.select-all-permissions').each(function() {
                var groupId = $(this).data('group'); // Group identifier from data-group attribute
                var allChecked = true;

                // Check if all checkboxes in the group are selected
                $('.permission-checkbox[data-group="' + groupId + '"]').each(function() {
                    if (!$(this).is(':checked')) {
                        allChecked = false; // Found an unchecked box
                        return false; // Exit loop
                    }
                });

                // Set the initial state of the "Select All" checkbox
                $(this).prop('checked', allChecked);
            });
        });
    </script>
@endpush
