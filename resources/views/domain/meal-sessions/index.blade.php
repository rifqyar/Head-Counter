@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('domain._alerts')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4">Meal Sessions</h1>
        <a href="{{ route('meal-sessions.create') }}" class="btn btn-primary">Create</a>
    </div>
    <table class="table table-striped">
        <thead><tr><th>Meeting</th><th>Name</th><th>Type</th><th>Window</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($sessions as $session)
            <tr>
                <td>{{ $session->meetingEvent?->event_name }}</td>
                <td>{{ $session->name }}</td>
                <td>{{ $session->entitlement_type->value ?? $session->entitlement_type }}</td>
                <td>{{ $session->starts_at?->format('d M H:i') }} - {{ $session->ends_at?->format('H:i') }}</td>
                <td><span class="badge badge-secondary">{{ $session->status->value ?? $session->status }}</span></td>
                <td class="text-right">
                    <a href="{{ route('meal-sessions.edit', $session) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="{{ route('meal-sessions.open', $session) }}" class="d-inline">@csrf<button class="btn btn-sm btn-success">Open</button></form>
                    <form method="POST" action="{{ route('meal-sessions.close', $session) }}" class="d-inline">@csrf<button class="btn btn-sm btn-secondary">Close</button></form>
                    <form method="POST" action="{{ route('meal-sessions.cancel', $session) }}" class="d-inline">@csrf<button class="btn btn-sm btn-danger">Cancel</button></form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $sessions->links() }}
</div>
@endsection
