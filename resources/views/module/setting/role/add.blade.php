<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Add Role</h3>
            </div>
        </div>
    </div>
    <section class="section row">
        <div class="card">
            <div class="card-body">
                <form class="form form-vertical" method="POST" action="{{ route('role.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required maxlength="255">
                    </div>
                    <button class="btn btn-primary">Submit</button>
                    <a href="javascript:void(0)" class="btn btn-warning" onclick="renderView(`{!! route('setting.role') !!}`)">Cancel</a>
                </form>
            </div>
        </div>
    </section>
</div>
