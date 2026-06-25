<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Subscriptions',
        'breadcrumbs' => ['Setting' => null, 'Subscriptions' => null],
    ])
    @include('domain._alerts')
    @include('domain._validation_summary')

    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Hotel</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Expires</th>
                        <th>Users</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($hotels as $hotel)
                        @php($subscription = $hotel->settings['subscription'] ?? [])
                        @php($subscriptionStatus = $subscription['status'] ?? 'TRIAL')
                        <tr>
                            <td>
                                <strong>{{ $hotel->code }}</strong><br>
                                <small class="text-muted">{{ $hotel->name }}</small>
                            </td>
                            <td>{{ $subscription['plan'] ?? 'Unassigned' }}</td>
                            <td>
                                <span class="badge badge-{{ $subscriptionStatus === 'ACTIVE' ? 'success' : ($subscriptionStatus === 'SUSPENDED' ? 'danger' : 'secondary') }}">
                                    {{ $subscriptionStatus }}
                                </span>
                            </td>
                            <td>{{ $subscription['expires_at'] ?? '-' }}</td>
                            <td>{{ $hotel->users_count }} / {{ $subscription['max_users'] ?? 'Unlimited' }}</td>
                            <td class="text-right">
                                <button class="btn btn-sm btn-outline-primary" type="button" data-toggle="collapse" data-target="#subscription-form-{{ $hotel->id }}">Edit</button>
                                <a class="btn btn-sm btn-outline-info spa_route" href="{{ route('hotels.show', $hotel) }}">Hotel</a>
                                <a class="btn btn-sm btn-outline-secondary spa_route" href="{{ route('users.index', ['hotel_id' => $hotel->id]) }}">Users</a>
                            </td>
                        </tr>
                        <tr class="collapse" id="subscription-form-{{ $hotel->id }}">
                            <td colspan="6">
                                <form method="POST" action="{{ route('settings.subscriptions.update', $hotel) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="form-row">
                                        <div class="form-group col-md-3">
                                            <label>Plan</label>
                                            <input class="form-control" name="plan" value="{{ old('plan', $subscription['plan'] ?? 'Enterprise') }}" required>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Status</label>
                                            <select class="form-control select2" name="status" required>
                                                @foreach (['TRIAL', 'ACTIVE', 'PAST_DUE', 'SUSPENDED', 'CANCELLED'] as $status)
                                                    <option value="{{ $status }}" @selected(old('status', $subscriptionStatus) === $status)>{{ $status }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Started At</label>
                                            <input class="form-control" type="date" name="started_at" value="{{ old('started_at', $subscription['started_at'] ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label>Expires At</label>
                                            <input class="form-control" type="date" name="expires_at" value="{{ old('expires_at', $subscription['expires_at'] ?? '') }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Max Users</label>
                                            <input class="form-control" type="number" min="1" name="max_users" value="{{ old('max_users', $subscription['max_users'] ?? '') }}">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" name="notes" rows="2">{{ old('notes', $subscription['notes'] ?? '') }}</textarea>
                                    </div>
                                    <button class="btn btn-primary js-disable-on-submit" type="submit">Save Subscription</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endcomponent

    {{ $hotels->links() }}
</div>
