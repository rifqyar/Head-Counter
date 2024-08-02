<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor"> Add Meeting Schedule <i class="mdi mdi-refresh refresh-page mt-2"
                    onclick="renderView(`{!! route('meeting-schedule.add') !!}`)"></i></h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Master Data</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)"
                        onclick="renderView(`{!! route('masterdata.meeting-schedule') !!}`)">Meeting Schedule</a></li>
                <li class="breadcrumb-item active">Add Meeting Schedule</li>
            </ol>
        </div>
    </div>
    <div class="row" id="validation">
        <div class="col-12">
            <div class="card wizard-content">
                <div class="card-body">
                    <h4 class="card-title">Create Meeting Schedule</h4>
                    <form action="javascript:void(0)" class="validation-wizard wizard-circle" id="add-meeting">
                        <!-- Step 1 -->
                        <h6>Choose Package</h6>
                        <section>
                            @include('module.MasterData.MeetingSchedule.formPackage')
                        </section>
                        <!-- Step 2 -->
                        <h6>Date & Meeting Rooms</h6>
                        <section>
                            @include('module.MasterData.MeetingSchedule.formDateRoom')
                        </section>
                        <!-- Step 3 -->
                        <h6>Booking Information</h6>
                        <section>
                            @include('module.MasterData.MeetingSchedule.formClient')
                        </section>
                        <!-- Step 4 -->
                        <h6>Confirmation</h6>
                        <section>
                            <div class="row form-material" id="preview">
                                <div class="col-md-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="">Client :</label>
                                        <input type="text" class="form-control required" id="preview-code"
                                            name="preview-code" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="">Package :</label>
                                        <input type="text" class="form-control required" id="preview-package-name"
                                            name="preview-package-name" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-4">
                                    <div class="form-group">
                                        <label for="">Room :</label>
                                        <input type="text" class="form-control required" id="preview-room"
                                            name="preview-rooms[]" disabled>
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="">Meeting Start Date : </label>
                                        <input disabled type="text" class="form-control required form-input"
                                            name="preview-tgl_start">
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-6">
                                    <div class="form-group">
                                        <label for="">Meeting End Date : </label>
                                        <input disabled type="text" class="form-control required form-input"
                                            name="preview-tgl_end">
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-6 ">
                                    <div class="form-group clockpicker" data-placement="bottom" data-align="top"
                                        data-autoclose="true">
                                        <label for="start">Jam Mulai : </label>
                                        <input disabled type="text" name="preview-jam_mulai"
                                            class="form-control form-input required">
                                    </div>
                                </div>
                                <div class="col-md-12 col-lg-6 ">
                                    <div class="form-group clockpicker" data-placement="bottom" data-align="top"
                                        data-autoclose="true">
                                        <label for="end">Jam Selesai : </label>
                                        <input disabled type="text" name="preview-jam_selesai"
                                            class="form-control form-input">
                                    </div>
                                </div>
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/masterdata/meetingschedule.js') }}"></script>
