<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor"> Client <i class="mdi mdi-refresh refresh-page mt-2" onclick="renderView(`{!! route('masterdata.client') !!}`)"></i></h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Master Data</a></li>
                <li class="breadcrumb-item active">Client</li>
            </ol>
        </div>
    </div>
    <section class="row">
        <div class="col-12">
            <div class="card">
                {{-- Action --}}
                <div class="card-header">
                    <div class="d-flex">
                        {{-- Left Nav --}}
                        <div class="">
                        </div>

                        {{-- Right Nav --}}
                        <div class="ml-auto">
                            <div class="float-end">
                                <!-- Tombol Tambah -->
                                {{-- @can('client.add') --}}
                                    <a href="javascript:void(0)" onclick="renderView(`{!! route('client.add') !!}`)"
                                        class="spa_route btn btn-icon icon-left btn-outline-primary rounded-pill"
                                        style="margin-right: 10px">
                                        <i class="fas fa-plus"></i> Tambah
                                    </a>
                                {{-- @endcan --}}

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive px-3">
                        <table class="display nowrap table table-hover table-striped table-bordered data-table" cellspacing="0" width="100%" id="example">
                            <thead class="text-muted table-light">
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Nama Perusahaan</th>
                                    <th class="text-center">Contact Person</th>
                                    <th class="text-center">Company Phone</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center" width="200px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@prepend('after-script')
<script type="text/javascript" src="{{ asset('js/module/masterdata/client.js') }}?2"></script>

