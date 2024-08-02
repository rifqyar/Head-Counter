<div class="row form-material">
    <div class="col-6">
        <label class="m-t-20" id="client">Choose Client</label>
        <select id="select-client-wizard" name="code_client" class="form-control form-input required select2-client"
            style="width: 100%" onchange="getClientDetail(this)">
            <option></option>
            @foreach ($client as $item)
                <option value="{{ $item->code }}">{{ $item->name }} ({{ $item->code }}) </option>
            @endforeach
        </select>
    </div>

    <div class="col-12 mt-5" id="container-client" style="display: none">
        <div class="row">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="code">Code Client</label>
                    <input readonly type="text" id="code" class="form-input form-control required"
                        name="code" placeholder="Client Code (Max 3 Character)" maxlength="3">
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="name">Nama Perusahaan</label>
                    <input readonly type="text" id="name" class="form-input form-control required"
                        name="name" placeholder="Company Name">
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="contact_person">Contact Person</label>
                    <input readonly type="text" id="contact_person" class="form-input form-control required"
                        name="contact_person" placeholder="Contact Person">
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="company_phone">Company Phone</label>
                    <input readonly type="text" id="company_phone" class="form-input form-control required"
                        name="company_phone" placeholder="Company Phone">
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input readonly type="email" id="email" class="form-input form-control required"
                        name="email" placeholder="Email">
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="kuota">Kuota</label>
                    <input type="number" id="kuota" class="form-input form-control required"
                        name="kuota" placeholder="kuota">
                </div>
            </div>
        </div>
    </div>
</div>
