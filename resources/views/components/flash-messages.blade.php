@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success',
            title: 'Done!',
            text: "{{ session('success') }}",
            timer: 3000,
            showConfirmButton: false,
            showCloseButton: true,
            toast: true,
            position: 'top-end',
            timerProgressBar: true
        });
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Oops!',
            text: "{{ session('error') }}",
            confirmButtonColor: '#ef4444',
            showCloseButton: true
        });
    });
</script>
@endif

@if(session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'warning',
            title: 'Wait!',
            text: "{{ session('warning') }}",
            confirmButtonColor: '#f59e0b',
            showCloseButton: true
        });
    });
</script>
@endif

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Check Form Errors',
            html: `<ul style="text-align: left; font-size: 0.9rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>`,
            confirmButtonColor: '#ef4444',
            showCloseButton: true
        });
    });
</script>
@endif
