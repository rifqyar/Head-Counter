<div class="container-fluid">
    <div class="row page-titles">
        <div class="col-md-5 col-8 align-self-center">
            <h3 class="text-themecolor"> Edit Meeting Schedule <i class="mdi mdi-refresh refresh-page mt-2"
                    onclick="renderView(`{!! route('meeting-schedule.edit', $data->id) !!}`)"></i></h3>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="javascript:void(0)">Master Data</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)"
                        onclick="renderView(`{!! route('masterdata.meeting-schedule') !!}`)">Meeting Schedule</a></li>
                <li class="breadcrumb-item active">Edit Meeting Schedule</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Edit Meeting Schedule</h4>
                    <form action="javascript:void(0)" id="edit-meeting">
                        <input type="hidden" name="id" value="{{ $data->id }}" class="form-input">
                        <div class="card">
                            <div class="card-body">
                                <div class="row form-material">
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="">Meeting Start Date</label>
                                            <input type="text" class="form-control required form-input"
                                                id="tgl_start" name="tgl_start"
                                                value="{{ \Carbon\Carbon::parse($data->tgl_start)->translatedFormat('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="form-group">
                                            <label for="">Meeting End Date</label>
                                            <input type="text" class="form-control required form-input"
                                                id="tgl_end" name="tgl_end"
                                                value="{{ \Carbon\Carbon::parse($data->tgl_end)->translatedFormat('Y-m-d') }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group clockpicker" data-placement="bottom" data-align="top"
                                            data-autoclose="true">
                                            <label for="start">Jam Mulai</label>
                                            <input type="text" name="jam_mulai" id="start"
                                                class="form-control form-input required"
                                                value="{{ \Carbon\Carbon::parse($data->jam_mulai)->translatedFormat('H:i') }}">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="form-group clockpicker" data-placement="bottom" data-align="top"
                                            data-autoclose="true">
                                            <label for="end">Jam Selesai</label>
                                            <input type="text" name="jam_selesai" id="end"
                                                class="form-control form-input"
                                                value="{{ \Carbon\Carbon::parse($data->jam_selesai)->translatedFormat('H:i') }}">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <div class="form-group">
                                            <label for="kuota">Kuota</label>
                                            <input type="integer" name="kuota" id="kuota"
                                                class="form-control form-input" value="{{ $data->kuota }}">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>Meeting Room</label>
                                            <div class="table-responsive table-striped px-3">
                                                <table class="table mb-0" id="example" style="width: 100%">
                                                    <thead class="table-primary">
                                                        <tr>
                                                            <th></th>
                                                            <th>Room Name</th>
                                                            <th>Room Availability</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php $i = 1 @endphp
                                                        @foreach ($rooms as $rd)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="form-group">
                                                                        <div class="radio-button">
                                                                            <input id="{{ $rd->kd_room }}"
                                                                                type="radio" name="rooms[]"
                                                                                class="form-input with-gap radio-col-orange"
                                                                                value="{{ $rd->kd_room }}"
                                                                                data-roomName="{{ $rd->name }}"
                                                                                @if ($rd->status->kd_status != App\Enums\RoomStatusEnum::Available) @if ($rd->kd_room == $data->room) checked @else disabled @endif
                                                                                @endif>
                                                                            <label for="{{ $rd->kd_room }}"> &nbsp;
                                                                            </label>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>{{ $rd->name }}</td>
                                                                <td>{{ $rd->status->name }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-end">
                            <div class="ml-auto">
                                <div class="float-end">
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/masterdata/meetingschedule.js') }}"></script>
