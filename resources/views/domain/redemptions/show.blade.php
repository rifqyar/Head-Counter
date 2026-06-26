<div class="container-fluid">
    @include('domain._alerts')
    @php($status = $redemption->status->value ?? $redemption->status)
    @php($code = $redemption->rejection_code?->value ?? $redemption->rejection_code)
    @include('domain._page_header', [
        'title' => 'Redemption '.$redemption->redemption_number,
        'breadcrumbs' => ['Operations' => null, 'Redemptions' => route('redemptions.index'), $redemption->redemption_number => null],
    ])
    @component('domain._card')
    <div class="row">
        <div class="col-lg-6">
            <table class="table table-sm">
                <tr><th>Status</th><td>{{ $status }}</td></tr>
                <tr><th>Rejection code</th><td>{{ $code ?: '-' }}</td></tr>
                <tr><th>Participant</th><td>{{ $redemption->participant?->full_name }}</td></tr>
                <tr><th>Meeting</th><td>{{ $redemption->meetingEvent?->event_name }}</td></tr>
                <tr><th>Meal session</th><td>{{ $redemption->mealSession?->name }}</td></tr>
                <tr><th>Original rejected record</th><td>{{ $redemption->originalRedemption?->redemption_number ?? '-' }}</td></tr>
                <tr><th>Created</th><td>{{ $redemption->created_at?->format('Y-m-d H:i:s') }}</td></tr>
            </table>
        </div>
        <div class="col-lg-6">
            @if ($status === 'REJECTED' && in_array($code, $overrideableCodes, true))
                <form method="POST" action="{{ route('redemptions.override', $redemption) }}" class="mb-3" onsubmit="return confirm('Override this rejected redemption?')">
                    @csrf
                    <div class="form-group"><label>Override reason</label><textarea name="reason" class="form-control" required></textarea></div>
                    <button class="btn btn-success">Override</button>
                </form>
            @endif
            @if (in_array($status, ['SUCCESS', 'OVERRIDDEN'], true))
                <form method="POST" action="{{ route('redemptions.reverse', $redemption) }}" onsubmit="return confirm('Reverse this redemption?')">
                    @csrf
                    <div class="form-group"><label>Reversal reason</label><textarea name="reason" class="form-control" required></textarea></div>
                    <button class="btn btn-warning">Reverse</button>
                </form>
            @endif
        </div>
    </div>
    <h5>Override Records</h5>
    <table class="table table-striped">
        <thead><tr><th>Number</th><th>Status</th><th>Reason</th><th>Created</th></tr></thead>
        <tbody>
        @forelse ($redemption->overrideRedemptions as $override)
            <tr><td><a href="{{ route('redemptions.show', $override) }}">{{ $override->redemption_number }}</a></td><td>{{ $override->status->value ?? $override->status }}</td><td>{{ $override->override_reason }}</td><td>{{ $override->created_at?->format('Y-m-d H:i') }}</td></tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">No linked override records.</td></tr>
        @endforelse
        </tbody>
    </table>
    @endcomponent
</div>
@include('domain._datatable')
