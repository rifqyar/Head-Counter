@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h4">Edit Meal Session</h1>
    <form method="POST" action="{{ route('meal-sessions.update', $session) }}">
        @csrf
        @method('PUT')
        @include('domain._alerts')
        @include('domain.meal-sessions.form')
    </form>
</div>
@endsection
