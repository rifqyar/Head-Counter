<div class="container-fluid">
    @include('domain._alerts')
    @include('domain._page_header', ['title' => 'Export Center', 'breadcrumbs' => ['Reports' => route('reports.index'), 'Exports' => null]])
    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Report</th><th>Format</th><th>Requested By</th><th>Status</th><th>Progress</th><th>Rows</th><th>Expires</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse ($exports as $export)
                        <tr>
                            <td>{{ $export->report_type }}</td>
                            <td>{{ strtoupper($export->format) }}</td>
                            <td>{{ $export->requester?->name }}</td>
                            <td><span class="badge badge-secondary">{{ $export->status->value ?? $export->status }}</span></td>
                            <td>{{ $export->progress }}%</td>
                            <td>{{ $export->row_count ?? '-' }}</td>
                            <td>{{ $export->expires_at ?? '-' }}</td>
                            <td>
                                @if (($export->status->value ?? $export->status) === 'COMPLETED')
                                    <a href="{{ route('reports.exports.download', $export) }}" class="btn btn-sm btn-success">Download</a>
                                @elseif (($export->status->value ?? $export->status) === 'FAILED')
                                    <span class="text-danger">{{ $export->error_message }}</span>
                                @else
                                    <span class="text-muted">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">No exports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $exports->links() }}
    @endcomponent
</div>
