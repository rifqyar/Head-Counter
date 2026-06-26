<div class="form-group mb-0">
    <button type="submit" class="btn btn-primary js-disable-on-submit">{{ $submitLabel ?? 'Save' }}</button>
    <a href="{{ $cancelUrl }}" class="btn btn-outline-secondary spa_route">
        <i class="mdi mdi-arrow-left"></i>
        {{ $backLabel ?? 'Back' }}
    </a>
</div>
