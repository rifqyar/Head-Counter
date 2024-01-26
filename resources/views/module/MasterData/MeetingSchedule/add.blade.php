@prepend('after-style')
<style>
    /*BBC/Sport/football/Scores&Fixtures*/
    .timeline-container {
    /*
    Note 1: because overflow-x: auto; hides the active div down arrow, expand the list div height by the height of the arrow.
    Height 60px = arrow size=6px + scroller height=54px
    */
    }
    .timeline-container .timeline-list {
    text-align: center;
    -ms-flex-wrap: nowrap !important;
    flex-wrap: nowrap !important;
    scrollbar-width: none;
    /* Firefox */
    -ms-overflow-style: none;
    /* IE 10+ */
    white-space: nowrap;
    overflow-x: auto;
    overflow-y: hidden;
    /*see Note 1*/
    height: 60px;
    }
    .timeline-container .timeline-list .active {
    background-color: #252525;
    border: none;
    }
    .timeline-container .timeline-list .active:hover {
    background-color: #252525;
    }
    .timeline-container .timeline-list .active:after {
    /*
    YOUTUBE: Create DIV Boxes with Arrows and Pointers, using CSS
    https://www.youtube.com/watch?v=s7JwxPnYoOw&t=175s
    */
    content: "";
    height: 0;
    width: 0;
    border-top: 6px solid #252525;
    border-right: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-left: 6px solid transparent;
    border-radius: 50%;
    top: 100%;
    left: 50%;
    position: absolute;
    /*take away arrow size=6px*/
    margin-left: -6px;
    }
    .timeline-container .timeline-list .active .timeline-date {
        color: #fff;
    }
    .timeline-container .timeline-list::-webkit-scrollbar {
    /* Chrome */
    width: 0;
    height: 0;
    }
    .timeline-container .timeline-item {
        display: inline-block;
        float: none;
        height: 54px; /*scroller height*/
        font-size: 0.875rem;
        text-transform: uppercase;
        background-color: #a2ecff;
        border-style: solid;
        border-color: #e2e3e5;
        border-top-width: 1px;
        border-right-width: 0;
        border-bottom-width: 1px;
        border-left-width: 1px;
        padding-top: 5px;
        padding-left: 0;
        padding-right: 0;
    }
    .timeline-container .timeline-item a:hover,
    .timeline-container .timeline-item a:visited,
    .timeline-container .timeline-item a:link,
    .timeline-container .timeline-item a:active {
        text-decoration: none;
        color: #252525;
    }
    .timeline-container .timeline-item:hover {
        background-color: #7ee1fa;
    }
    .timeline-container .timeline-item:active {
        background-color: #6698a4;
    }
    .timeline-container .prev-btn {
        text-align: center;
        color: #252525;
        cursor: pointer;
        font-size: 2rem;
        background-color: #e9ecef;
        padding: 0;
        height: 54px;
        border-top-left-radius: 25%;
        border-bottom-left-radius: 25%;
    }
    .timeline-container .next-btn {
        text-align: center;
        color: #252525;
        cursor: pointer;
        font-size: 2rem;
        background-color: #e9ecef;
        padding: 0;
        height: 54px;
        border-top-right-radius: 25%;
        border-bottom-right-radius: 25%;
    }
    .timeline-container .prev-btn:hover,
    .timeline-container .next-btn:hover {
        background-color: #e2e3e5;
    }

</style>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Add Meeting Schedule <i class="fas fa-refresh refresh-page" onclick="renderView(`{!!route('meeting-schedule.add')!!}`)"></i> </h3>
                <p class="text-subtitle text-muted"></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">Master Data</a></li>
                        <li class="breadcrumb-item spa_route" aria-current="page"><a href="javascript:void(0)" onclick="renderView(`{!!route('masterdata.meeting-schedule')!!}`)">Meeting Schedule</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Add</a></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section row">
        <div class="card">
            <div class="card-body">
                <form class="form form-vertical" id="add-meeting" action="javascript:void(0)">
                    @csrf
                    <!--BBC/Sport/football/Scores&Fixtures-->
                    <h4>Available Date</h4>
                    <div class="container-fluid timeline-container">
                        <div class="row">
                            <div class="col-sm-1 d-none d-sm-block">
                                <div class="row">
                                    <div class="col-12 prev-btn">
                                        <span class="fa fa-angle-left"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-10">
                                <div>
                                    <div class="row timeline-list">
                                        @foreach ($avail_date as $date)
                                            <div class="col-3 col-sm-2 col-lg-1 timeline-item">
                                                <a href="#" class="timeline-date" onclick="setTglMeeting(this)" data-tgl="{{$date}}">
                                                    <span class="d-block"><strong>{{\Carbon\Carbon::parse($date)->translatedFormat('D')}}</strong></span>
                                                    <span class="d-block">{{\Carbon\Carbon::parse($date)->translatedFormat('d/M')}}</span>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-1 d-none d-sm-block">
                                <div class="row">
                                    <div class="col-12 next-btn">
                                        <span class="fa fa-angle-right"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-body mt-4" style="display: none">
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="tgl">Tgl. Meeting</label>
                                    <input type="text" class="form-input form-control required" name="tgl_meeting" readonly>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="client">Pilih Client</label>
                                    <select id="client" name="code_client" class="form-control form-input required" data-placeholder="Harap Pilih Client">
                                        <option></option>
                                        @foreach ($client as $item)
                                            <option value="{{ $item->code }}">{{ $item->name }} ({{$item->code}}) </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="start">Jam Mulai</label>
                                    <input type="time" name="jam_mulai" id="start" class="form-control form-input required">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="end">Jam Selesai</label>
                                    <input type="time" name="jam_selesai" id="end" class="form-control form-input">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="form-group">
                                    <label for="slot">Slot</label>
                                    <input type="text" name="kuota" id="slot" class="form-control form-input required">
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <label for="">Langsung generate QR Code</label>
                                <div class="form-check">
                                    <input class="form-check-input form-input required" type="radio" name="generateQR" id="generateQR1" value="ya">
                                    <label class="form-check-label" for="generateQR1">
                                        Ya
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input form-input required" type="radio" name="generateQR" id="generateQR2" value="tidak">
                                    <label class="form-check-label" for="generateQR2">
                                        Tidak
                                    </label>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" id="btn-save" class="btn btn-primary me-1 mb-1">Submit</button>
                                <button type="button" class="btn btn-warning me-1 mb-1"
                                    onclick="renderView(`{!! route('masterdata.meeting-schedule') !!}`)">Cancel</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </section>

    @prepend('after-script')
    <script type="text/javascript" src="{{ asset('js/module/masterdata/meetingschedule.js') }}"></script>
</div>
