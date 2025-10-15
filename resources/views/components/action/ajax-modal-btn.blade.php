@props(['title', 'href'])
<button type="button" {{ $attributes->merge(['class' => 'btn bg-indigo-800']) }}
    onclick="openModal('{{ $href }}', '{{ $title }}')">
    {{ $title }} <i class="icon-plus3 ml-2"></i>
</button>

<script>
    function openModal(url, title) {
        $('#ajaxModal .modal-title').text(title);
        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                $('#ajaxModal .modal-body').html(data);
                $('#ajaxModal').show();
            },
            error: function(xhr) {
                console.error('Error loading form:', xhr);
            }
        });
    }
</script>
