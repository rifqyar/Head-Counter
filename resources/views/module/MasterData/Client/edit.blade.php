<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor">Edit Client <i class="mdi mdi-refresh refresh-page mt-2" onclick="renderView(`{!! route('client.edit', $client->id) !!}`)"></i></h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Master Data</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)" onclick="renderView(`{!! route('masterdata.client') !!}`)">Client</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
    <section class="section row">
        <div class="card">
            <div class="card-body">
                <form class="form form-vertical" id="edit-client" action="javascript:void(0)">
                    @csrf
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="code">Code Client</label>
                                    <input type="text" id="clientCode" class="form-input form-control required" name="code" value="{{ $client->code }}" maxlength="3">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="name">Nama Perusahaan</label>
                                    <input type="text" id="companyName" class="form-input form-control required" name="name" value="{{ $client->name }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="contact_person">Contact Person</label>
                                    <input type="text" id="contactPerson" class="form-input form-control required" name="contact_person" value="{{ $client->contact_person }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="company_phone">Company Phone</label>
                                    <input type="text" id="companyPhone" class="form-input form-control required" name="company_phone" value="{{ $client->company_phone }}">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="clientEmail" class="form-input form-control required" name="email" value="{{ $client->email }}">
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-end">
                                <button type="button" id="btn-update-client" class="btn btn-primary mr-1 mb-1" data-url="{{ route('client.update', $client->id) }}">Submit</button>
                                <button type="button" class="btn btn-warning ml-1 mb-1" onclick="renderView(`{!! route('masterdata.client') !!}`)">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    @prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/masterdata/client.js') }}?2"></script>
    @endprepend
</div>
