@if (session('status'))
    <div class="alert alert-success hc-alert">
        <i class="mdi mdi-check-circle"></i>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger hc-alert">
        <i class="mdi mdi-alert-circle"></i>
        <div>
            <strong>Please fix the validation errors below.</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
