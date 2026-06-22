<div class="container-fluid">
    @include('domain._page_header', ['title' => 'Participant Entitlement Scanner', 'breadcrumbs' => ['Operations' => null, 'Scanner' => null]])
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div id="scanner-app" data-redeem-endpoint="{{ route('api.v1.scanner.redeem') }}" data-validate-endpoint="{{ route('api.v1.scanner.validate') }}" data-csrf="{{ csrf_token() }}"></div>
            <div id="scan-panel" class="p-4 text-white bg-secondary">
                <h1 class="h4">Participant Entitlement Scanner</h1>
                <p class="mb-3">Scan the participant QR when serving lunch, coffee break, or another package entitlement. A successful scan confirms the participant and consumes one entitlement for the selected session.</p>
                <div class="form-group">
                    <label>Entitlement Session</label>
                    <select id="meal_session_id" class="form-control">
                        @foreach ($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }} - {{ $session->entitlement_type->value ?? $session->entitlement_type }} - {{ $session->meetingEvent?->event_name }} - {{ $session->status->value ?? $session->status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Mode</label>
                    <select id="scan_mode" class="form-control">
                        <option value="redeem">Redeem entitlement and confirm participant</option>
                        <option value="validate">Validate only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Camera</label>
                    <select id="camera_id" class="form-control mb-2" disabled></select>
                    <div id="camera-preview" class="bg-dark mb-2" style="min-height: 260px;"></div>
                    <button id="camera-start" type="button" class="btn btn-light" disabled>Start camera</button>
                    <button id="camera-stop" type="button" class="btn btn-outline-light" disabled>Stop camera</button>
                    <div id="camera-message" class="small mt-2"></div>
                </div>
                <div class="form-group">
                    <label>Participant QR Token or URL</label>
                    <textarea id="qr_token" class="form-control" rows="3" autocomplete="off"></textarea>
                </div>
                <button id="redeem-btn" type="button" class="btn btn-light btn-lg btn-block">Scan Entitlement</button>
                <pre id="scan-result" class="mt-3 text-white"></pre>
            </div>
        </div>
    </div>
</div>
<script>
    window.HeadCounterScanner && window.HeadCounterScanner.init && window.HeadCounterScanner.init();
</script>
