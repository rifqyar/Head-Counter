<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row pricing-plan" style="border: 0 !important;" >
                    @foreach ($package as $pck)
                        <div class="col-md-4 col-xs-12 col-sm-6">
                            <div class="pricing-box py-3 px-2 waves-effect waves-light" style="border: 0 !important">
                                <div class="pricing-body b-l">
                                    <div class="pricing-header bg-primary text-white mb-3 py-3">
                                        <h4 class="text-center text-white">{{$pck->name}}</h4>
                                        <h6 class="text-white"><span class="price-sign">IDR </span>{{$pck->price}}</h6>
                                    </div>
                                    <div class="price-table-content text-left">
                                        {!! $pck->details !!}
                                        <div class="price-row text-center">
                                            <button class="btn btn-info waves-effect waves-light m-t-20">Choose Package</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
