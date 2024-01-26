<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Manage Permission <i class="fas fa-refresh refresh-page" onclick="renderView(`{!!route('role.manage-permission', $role->id)!!}`)"></i> </h3>
                <p class="text-subtitle text-muted"></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">Setting</a></li>
                        <li class="breadcrumb-item spa_route" aria-current="page"><a href="javascript:void(0)" onclick="renderView(`{!!route('setting.role')!!}`)">Role</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Manage Permission</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section row">
        <div class="card">
            <div class="card-body">
                <form class="form form-vertical" id="manage-permission" action="javascript:void(0)">
                    @csrf
                    <div class="form-body">
                        <input type="text" class="form-control form-input" placeholder="Nama" name="role_id"
                        value="{{ $role->id }}" hidden>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $value)
                                        <tr>
                                            <td>{{ $value->name }}</td>
                                            <td><input class="form-check-input form-input" type="checkbox" value="{{ $value->name }}"
                                                    name="permissions[]"
                                                    {{ in_array($value->name, $myPermissions) ? 'checked' : '' }}></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" id="btn-save" class="btn btn-primary me-1 mb-1">Submit</button>
                            <button type="button" class="btn btn-warning me-1 mb-1" onclick="renderView(`{!!route('setting.role')!!}`)" >Cancel</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </section>

    @prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/setting/role.js') }}"></script>
</div>
