<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Setting Permission <i class="fas fa-refresh refresh-page" onclick="renderView(`{!! route('setting.permission') !!}`)"></i>
                </h3>
                <p class="text-subtitle text-muted"></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">Setting</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Permission</a></li>
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
                            <a href="javascript:void(0)" onclick="renderView(`{!! route('permission.add') !!}`)"
                                class="spa_route btn btn-icon icon-left btn-outline-primary rounded-pill"
                                style="margin-right: 10px">
                                <i class="fas fa-plus"></i> Tambah
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive table-card px-3">
                    <table class="table table-centered align-middle table-nowrap mb-0 data-table" style="width: 100%">
                        <thead class="text-muted table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Permission Name</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@prepend('after-script')
<script type="text/javascript" src="{{ asset('js/module/setting/permission.js') }}"></script>

