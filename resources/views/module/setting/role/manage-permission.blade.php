<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Role Permissions',
        'breadcrumbs' => ['Setting' => null, 'Roles' => route('setting.role'), $role->name => null],
    ])

    @component('domain._card')
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-1">{{ $role->name }}</h5>
                <span class="text-muted">{{ count($myPermissions) }} of {{ $permissions->count() }} permissions selected</span>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-sm btn-outline-primary" id="check-all-permissions">Select All</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-permissions">Clear</button>
            </div>
        </div>

        <form class="form form-vertical" id="manage-permission" action="javascript:void(0)">
            @csrf
            <input type="hidden" class="form-control form-input" name="role_id" value="{{ $role->id }}">

            <div class="row">
                @foreach ($permissions->groupBy(fn ($permission) => \Illuminate\Support\Str::before($permission->name, '.')) as $group => $items)
                    <div class="col-md-6 col-xl-4 mb-3">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-uppercase text-muted mb-3">{{ $group }}</h6>
                            @foreach ($items as $permission)
                                <div class="custom-control custom-checkbox mb-2">
                                    <input class="custom-control-input form-input permission-checkbox" id="permission-{{ $permission->id }}" type="checkbox" value="{{ $permission->name }}" name="permissions[]" {{ in_array($permission->name, $myPermissions, true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="permission-{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" id="btn-save" class="btn btn-primary">Save Permissions</button>
            <button type="button" class="btn btn-link" onclick="renderView(`{!! route('setting.role') !!}`)">Cancel</button>
        </form>
    @endcomponent
</div>

<script type="text/javascript" src="{{ asset('js/module/setting/role.js') }}"></script>
