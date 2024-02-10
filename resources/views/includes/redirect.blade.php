@extends('layouts.app')

@section('title')
    Hotel Management App
@endsection
@section('content')
    <div id="render">
        <div class="container-fluid">
            <div class="row justify-content-center align-items-center" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0">
                <div class="text-center">
                    <div class="spinner-border text-primary spinner-border-lg" role="status">
                      <span class="sr-only">Loading...</span>
                    </div>
                    <br>
                    <strong>Please Wait ....</strong>
                </div>
            </div>
        </div>
    </div>
@endsection
