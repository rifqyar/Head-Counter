<div class="container-fluid">
@include('domain._page_header', ['title' => 'Create User', 'breadcrumbs' => ['Security' => null, 'Users' => route('users.index'), 'Create' => null]])
@include('domain._alerts')
@include('domain._validation_summary')
@component('domain._card')
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    @include('domain.users.form')
    @include('domain._form_actions', ['cancelUrl' => route('users.index')])
</form>
@endcomponent
</div>
