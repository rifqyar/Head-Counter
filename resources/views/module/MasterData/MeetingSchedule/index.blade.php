<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor"> Meeting Schedule <i class="mdi mdi-refresh refresh-page mt-2" onclick="renderView(`{!! route('masterdata.meeting-schedule') !!}`)"></i></h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Master Data</a></li>
                <li class="breadcrumb-item active">Meeting Schedule</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-no-border">
                {{-- Action --}}
                <div class="card-header">
                    <div class="d-flex">
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h5>Filter</h5>
                        <form action="javascript:void(0)" id="form-filter">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="client">Client</label>
                                            <input type="text" name="client" class="form-control form-input" placeholder="Masukan Kode Client atau Nama Client">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="tgl">Tgl. Acara</label>
                                            <input type="date" value="{{date('Y-m-d')}}" name="tgl" class="form-control form-input">
                                        </div>
                                    </div>
                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="button" class="btn btn-warning mr-1 mb-1" onclick="resetFilter()">Reset Filter</button>
                                        <button type="submit" id="btn-save" class="btn btn-primary ml-1 mb-1">Filter Data</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive px-3">
                        <table class="table mb-0 data-table" id="example" style="width: 100%">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Client</th>
                                    <th class="text-center">Tgl. Meeting</th>
                                    <th class="text-center">Jam Mulai</th>
                                    <th class="text-center">Jam Selesai</th>
                                    <th class="text-center">Kuota</th>
                                    <th class="text-center">Ruangan</th>
                                    <th class="text-center">Paket</th>
                                    <th class="text-center" width="200px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@prepend('after-script')
<script type="text/javascript" src="{{ asset('js/module/transaction/meetingschedule.js') }}?2"></script>

