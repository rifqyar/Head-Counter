<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Redemptions', 'breadcrumbs' => ['Operations' => null, 'Redemptions' => null]])
    @component('domain._card')
        <form method="GET" class="form-row mb-3">
            <div class="col"><input name="participant_id" value="{{ request('participant_id') }}" class="form-control" placeholder="Participant ID"></div>
            <div class="col"><input name="meeting_event_id" value="{{ request('meeting_event_id') }}" class="form-control" placeholder="Meeting ID"></div>
            <div class="col"><input name="meal_session_id" value="{{ request('meal_session_id') }}" class="form-control" placeholder="Session ID"></div>
            <div class="col"><input name="rejection_code" value="{{ request('rejection_code') }}" class="form-control" placeholder="Rejection code"></div>
            <div class="col"><input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control"></div>
            <div class="col"><input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control"></div>
            <div class="col-auto"><button class="btn btn-primary">Filter</button></div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Number</th><th>Participant</th><th>Meeting</th><th>Session</th><th>Status</th><th>Rejected</th><th>Original</th><th></th></tr></thead>
                <tbody>
                @foreach ($redemptions as $redemption)
                    @php($status = $redemption->status->value ?? $redemption->status)
                    @php($code = $redemption->rejection_code?->value ?? $redemption->rejection_code)
                    <tr>
                        <td><a href="{{ route('redemptions.show', $redemption) }}" class="spa_route">{{ $redemption->redemption_number }}</a></td>
                        <td>{{ $redemption->participant?->full_name }}</td>
                        <td>{{ $redemption->meetingEvent?->event_name }}</td>
                        <td>{{ $redemption->mealSession?->name }}</td>
                        <td><span class="badge badge-secondary">{{ $status }}</span></td>
                        <td>{{ $code }}</td>
                        <td>{{ $redemption->originalRedemption?->redemption_number ?? '-' }}</td>
                        <td class="text-right">
                            @if ($status === 'REJECTED' && in_array($code, $overrideableCodes, true))
                                <form method="POST" action="{{ route('redemptions.override', $redemption) }}" class="d-inline" onsubmit="return confirm('Override this rejected redemption?')">@csrf<input name="reason" class="form-control form-control-sm d-inline-block" style="width: 180px;" required placeholder="Override reason"><button class="btn btn-sm btn-success">Override</button></form>
                            @endif
                            @if (in_array($status, ['SUCCESS', 'OVERRIDDEN'], true))
                                <form method="POST" action="{{ route('redemptions.reverse', $redemption) }}" class="d-inline" onsubmit="return confirm('Reverse this redemption?')">@csrf<input name="reason" class="form-control form-control-sm d-inline-block" style="width: 180px;" required placeholder="Reversal reason"><button class="btn btn-sm btn-warning">Reverse</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $redemptions->links() }}
    @endcomponent
</div>
