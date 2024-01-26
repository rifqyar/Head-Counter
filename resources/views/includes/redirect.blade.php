@extends('layouts.app')

@section('title')
    PM - EDII
@endsection
@section('content')
    <div id="render">
        <div class="page-heading">
            <div class="page-title">
                <div class="row">
                    <div class="col-12 col-md-6 order-md-1 order-last">
                        <h3>...</h3>
                        <p class="text-subtitle text-muted"></p>
                    </div>
                    <div class="col-12 col-md-6 order-md-2 order-first">
                        <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">...</a></li>
                                <li class="breadcrumb-item active" aria-current="page">...</a></li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            <section class="section row">
                <div class="card">
                    {{-- <ul class="list-group sortable p-5" style="list-style: none">
                    {!!$menus!!}
                </ul> --}}

                    {{-- Action --}}
                    <div class="card-header">
                        <div class="row">
                            {{-- Left Nav --}}
                            <div class="col-12 col-md-6 order-md-1 order-last">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="overview-tab" data-bs-toggle="tab" href="#overview"
                                            role="tab" aria-controls="overview" aria-selected="true">...</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="menus-tab" data-bs-toggle="tab" href="#menus"
                                            role="tab" aria-controls="menus" aria-selected="false"
                                            tabindex="-1">...</a>
                                    </li>
                                </ul>
                            </div>

                            {{-- Right Nav --}}
                            <div class="col-12 col-md-6 order-md-2 order-first ">
                                <div class="float-start float-lg-end">



                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade active show" id="menus" role="tabpanel"
                                aria-labelledby="menu-tab">
                                ...
                            </div>
                            <div class="tab-pane fade" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                                ...
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="modal fade text-left" id="modal-detail-delete" tabindex="-1" role="dialog"
                aria-labelledby="myModalLabel19" aria-modal="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="myModalLabel19">Small Modal</h4>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-x">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="modal-body">
                            <span>Deleted By : <span id="deleted_by"></span> </span>
                            <br>
                            <span>Deleted At : <span id="deleted_at"></span> </span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary btn-sm" data-bs-dismiss="modal">
                                <i class="bx bx-x d-block d-sm-none"></i>
                                <span class="d-sm-block d-none">Close</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
