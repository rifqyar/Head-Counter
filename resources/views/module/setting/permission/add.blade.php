<div class="container-fluid">
    @include('domain._page_header', [
        'title' => 'Create Permission',
        'breadcrumbs' => ['Setting' => null, 'Permissions' => route('setting.permission'), 'Create' => null],
    ])

    @component('domain._card')
        <form class="form form-vertical" id="add-permission" action="javascript:void(0)">
            @csrf
            <div class="form-group">
                <label>Permission Name</label>
                <input type="text" id="permission_name" class="form-input form-control required" name="name" placeholder="Example: booking.approve">
            </div>
            <button type="submit" id="btn-save" class="btn btn-primary">Save Permission</button>
            <button type="button" class="btn btn-link" onclick="renderView(`{!! route('setting.permission') !!}`)">Cancel</button>
        </form>
    @endcomponent
</div>

<script type="text/javascript" src="{{ asset('js/module/setting/permission.js') }}"></script>
