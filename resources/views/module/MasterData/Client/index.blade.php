@prepend('after-style')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Master Data Client <i class="fas fa-refresh refresh-page" onclick="renderView(`{!! route('masterdata.client') !!}`)"></i>
                    </h3>
                    <p class="text-subtitle text-muted"></p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">Master Data</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Client</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section row">
            <div class="card">
                {{-- Action --}}
                <div class="card-header">
                    <div class="row">
                        {{-- Left Nav --}}
                        <div class="col-12 col-md-6 order-md-1 order-last">
                        </div>

                        {{-- Right Nav --}}
                        <div class="col-12 col-md-6 order-md-2 order-first ">
                            <div class="float-start float-lg-end">
                                <!-- Tombol Tambah -->
                                @can('client.add')
                                    <a href="javascript:void(0)" onclick="renderView(`{!! route('client.add') !!}`)"
                                        class="spa_route btn btn-icon icon-left btn-outline-primary rounded-pill"
                                        style="margin-right: 10px">
                                        <i class="fas fa-plus"></i> Tambah
                                    </a>
                                @endcan

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade active show" id="clients" role="tabpanel" aria-labelledby="menu-tab">
                            <div class="table-responsive table-card px-3">
                                <table class="table table-centered align-middle table-nowrap mb-0 data-table" id="example"
                                    style="width: 100%">
                                    <thead class="text-muted table-light">
                                        <tr>
                                            <th class="text-center">No</th>
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
            </div>
        </section>
    </div>
@prepend('after-script')
<script type="text/javascript" src="{{ asset('js/module/masterdata/client.js') }}"></script>

