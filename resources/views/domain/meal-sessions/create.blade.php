@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h4">Create Meal Session</h1>
    <form method="POST" action="{{ route('meal-sessions.store') }}">
        @csrf
        @include('domain._alerts')
        @include('domain.meal-sessions.form')
    </form>
</div>
@endsection
