<div class="card">
    <div class="card-body">
        <div class="row form-material">
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="">Meeting Start Date</label>
                    <input type="text" class="form-control required form-input" id="tgl_start" name="tgl_start"
                        value="{{ \Carbon\Carbon::now()->translatedFormat('Y-m-d') }}">
                </div>
            </div>
            <div class="col-md-6 col-12">
                <div class="form-group">
                    <label for="">Meeting End Date</label>
                    <input type="text" class="form-control required form-input" id="tgl_end" name="tgl_end"
                        value="{{ \Carbon\Carbon::now()->translatedFormat('Y-m-d') }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group clockpicker" data-placement="bottom" data-align="top" data-autoclose="true">
                    <label for="start">Jam Mulai</label>
                    <input type="text" name="jam_mulai" id="start" class="form-control form-input required"
                        value="{{ \Carbon\Carbon::now()->translatedFormat('H:i') }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group clockpicker" data-placement="bottom" data-align="top" data-autoclose="true">
                    <label for="end">Jam Selesai</label>
                    <input type="text" name="jam_selesai" id="end" class="form-control form-input"
                        value="{{ \Carbon\Carbon::now()->translatedFormat('H:i') }}">
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
                                                <div class="checkbox checkbox-success">
                                                    <input id="{{ $rd->kd_room }}" type="checkbox" name="rooms[]" class="form-input" value="{{ $rd->kd_room }}" data-roomName="{{$rd->name}}" @if($rd->status->kd_status != App\Enums\RoomStatusEnum::Available) disabled @endif>
                                                    <label for="{{ $rd->kd_room }}"> &nbsp; </label>
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
