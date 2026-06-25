<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Manage Roles',
        'breadcrumbs' => ['Setting' => null, 'Roles' => null],
        'actions' => new \Illuminate\Support\HtmlString('<a href="javascript:void(0)" onclick="renderView(`'.route('role.add').'`)" class="btn btn-primary"><i class="fa fa-plus mr-1"></i> Create Role</a>'),
    ])
    @include('domain._alerts')

    <div class="row">
        <div class="col-md-4">
            @component('domain._card')
                <div class="d-flex align-items-center">
                    <i class="fa fa-users fa-2x text-primary mr-3"></i>
                    <div>
                        <small class="text-muted">Assignable Roles</small>
                        <h4 class="mb-0">{{ $roleCount }}</h4>
                    </div>
                </div>
            @endcomponent
        </div>
        <div class="col-md-4">
            @component('domain._card')
                <div class="d-flex align-items-center">
                    <i class="fa fa-lock fa-2x text-info mr-3"></i>
                    <div>
                        <small class="text-muted">Manageable Permissions</small>
                        <h4 class="mb-0">{{ $permissionCount }}</h4>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>

    @component('domain._card')
        <div class="table-responsive">
            <table class="table table-striped table-hover data-table" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 70px">No</th>
                        <th>Role Name</th>
                        <th style="width: 160px">Permissions</th>
                        <th class="text-right" style="width: 180px">Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endcomponent
</div>

<script type="text/javascript" src="{{ asset('js/module/setting/role.js') }}"></script>
