<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Permission <i class="fas fa-refresh refresh-page" onclick="renderView(`{!! route('permission.edit', $permission->id) !!}`)"></i> </h3>
                <p class="text-subtitle text-muted"></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">Setting</li>
                        <li class="breadcrumb-item spa_route" aria-current="page"><a href="javascript:void(0)" onclick="renderView(`{!! route('setting.permission') !!}`)">Permission</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section row">
        <div class="card">
            <div class="card-body">
                <form class="form form-vertical" id="edit-permission" action="javascript:void(0)">
                    @csrf
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="nama">Nama Permission</label>
                                    <input type="text" id="nama" class="form-input form-control required" name="name" value="{{ $permission->name }}">
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="button" id="btn-update-permission" data-url="{{ route('permission.update', $permission->id) }}" class="btn btn-primary me-1 mb-1">Submit</button>
                                <button type="button" class="btn btn-warning me-1 mb-1" onclick="renderView(`{!! route('setting.permission') !!}`)">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/setting/permission.js') }}"></script>
    @endprepend
</div>
