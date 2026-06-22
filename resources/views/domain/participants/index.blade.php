<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', [
        'title' => 'Participants',
        'breadcrumbs' => ['Operations' => null, 'Participants' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="'.route('participants.create').'" class="btn btn-primary spa_route"><i class="mdi mdi-plus"></i> Register Participant</a>'),
    ])
    @component('domain._card')
        <form method="GET" action="{{ route('participants.index') }}" class="mb-3">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label>Meeting</label>
                    <select name="meeting_event_id" class="form-control">
                        <option value="">All meetings</option>
                        @foreach ($meetings as $meeting)
                            <option value="{{ $meeting->id }}" @selected((string) ($filters['meeting_event_id'] ?? '') === (string) $meeting->id)>
                                {{ $meeting->event_name }} - {{ $meeting->start_at?->format('d M Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Client</label>
                    <select name="client_id" class="form-control">
                        <option value="">All clients</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) ($filters['client_id'] ?? '') === (string) $client->id)>{{ $client->company_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Meeting Date</label>
                    <input type="date" name="meeting_date" class="form-control" value="{{ $filters['meeting_date'] ?? '' }}">
                </div>
                <div class="form-group col-md-2">
                    <button class="btn btn-primary btn-block">Filter</button>
                    <a href="{{ route('participants.index') }}" class="btn btn-outline-secondary btn-block spa_route">Reset</a>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-striped canonical-datatable">
                <thead><tr><th>Number</th><th>Name</th><th>Meeting</th><th>Client</th><th>Date</th><th>Email</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                @forelse ($participants as $participant)
                    <tr>
                        <td>{{ $participant->participant_number }}</td>
                        <td>{{ $participant->full_name }}</td>
                        <td>{{ $participant->meetingEvent?->event_name ?? '-' }}</td>
                        <td>{{ $participant->meetingEvent?->booking?->client?->company_name ?? '-' }}</td>
                        <td>{{ $participant->meetingEvent?->start_at?->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>{{ $participant->email }}</td>
                        <td>{{ $participant->phone }}</td>
                        <td><span class="badge badge-secondary">{{ $participant->status->value ?? $participant->status }}</span></td>
                        <td><a href="{{ route('participants.show', $participant) }}" class="btn btn-sm btn-info spa_route">Detail</a> <a href="{{ route('participants.edit', $participant) }}" class="btn btn-sm btn-warning spa_route">Edit</a> @can('participant.qr.manage')<a href="{{ route('participants.qr.show', $participant) }}" class="btn btn-sm btn-outline-info spa_route">QR</a>@endcan</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted">No participants found for the selected filters.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $participants->links() }}
    @endcomponent
</div>
@include('domain._datatable')
