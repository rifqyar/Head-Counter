<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Create Role',
        'breadcrumbs' => ['Setting' => null, 'Roles' => route('setting.role'), 'Create' => null],
    ])

    @component('domain._card')
        <form class="form form-vertical" id="add-role" action="javascript:void(0)">
            @csrf
            <div class="form-group">
                <label>Role Name</label>
                <input type="text" id="role_name" class="form-control form-input required" name="name" required maxlength="255" placeholder="Example: EVENT_SUPERVISOR">
            </div>
            <button class="btn btn-primary" id="btn-save-role" type="submit">Save Role</button>
            <button type="button" class="btn btn-link" onclick="renderView(`{!! route('setting.role') !!}`)">Cancel</button>
        </form>
    @endcomponent
</div>

<script type="text/javascript" src="{{ asset('js/module/setting/role.js') }}"></script>
