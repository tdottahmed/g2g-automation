@props(['actions'])

<div class="dropdown d-inline-block">
    <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ri-more-fill align-middle"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        @foreach ($actions as $key => $action)
            <li>
                @if (Str::lower($action['method']) == 'delete' || Str::lower($action['method']) == 'post')
                    <form action="{{ $action['route'] }}" method="POST" class="delete-form"
                        data-action="{{ $action['route'] }}" data-method="{{ Str::upper($action['method']) }}">
                        @if (Str::lower($action['method']) == 'delete')
                            @method('DELETE')
                        @endif
                        @csrf
                        <button type="button" class="dropdown-item remove-item-btn">
                            <i class="{{ $action['icon'] }} align-bottom me-2 text-muted"></i> {{ $action['title'] }}
                        </button>
                    </form>
                @else
                    <a href="{{ $action['route'] }}" class="dropdown-item"><i
                            class="{{ $action['icon'] }} align-bottom me-2 text-muted"></i> {{ $action['title'] }}</a>
                @endif
            </li>
        @endforeach
    </ul>
</div>

@push('scripts')
    <script>
        document.querySelectorAll('.remove-item-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    customClass: {
                        confirmButton: 'btn btn-primary w-xs me-2 mt-2',
                        cancelButton: 'btn btn-danger w-xs mt-2',
                    },
                    confirmButtonText: 'Yes, delete it!',
                    buttonsStyling: false,
                    showCloseButton: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
