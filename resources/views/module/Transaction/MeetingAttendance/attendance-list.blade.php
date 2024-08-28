<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor"> Meeting Attendance <i class="mdi mdi-refresh refresh-page mt-2"
                    onclick="renderView(`{!! route('meeting-attendance.attendance-list', $trx_meeting) !!}`)"></i></h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Transaction</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)" onclick="renderView(`{!! route('transaction.meeting-attendance') !!}`)"> Meeting Attendance </a></li>
                <li class="breadcrumb-item active">Attendance List</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-no-border">
                {{-- Action --}}
                <div class="card-header">
                    <h5>Daftar Hadir Meeting</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive px-3">
                        <table class="table mb-0 attendance-list" id="attendance-list" style="width: 100%">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">Nomor Telpon</th>
                                    <th class="text-center">Perusahaan</th>
                                    <th class="text-center">Jabatan </th>
                                    <th class="text-center">QR Scanned</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attendance as $key => $dt)
                                    @php
                                        $i = $key + 1;
                                    @endphp
                                    <tr>
                                        <td>{{ $i }}</td>
                                        <td>{{ $dt->name }}</td>
                                        <td>{{ $dt->phone_number }}</td>
                                        <td>{{ $dt->company }}</td>
                                        <td>{{ $dt->jabatan }}</td>
                                        <td>{{ $dt->scanned_qr == 0 ? 'Belum discan' : 'Sudah discan' }}</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/transaction/attendance.js') }}?2"></script>
