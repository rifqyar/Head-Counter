<div class="container-fluid">
@include('domain._page_header', ['title' => 'Edit User', 'breadcrumbs' => ['Security' => null, 'Users' => route('users.index'), 'Edit' => null]])
@include('domain._alerts')
@include('domain._validation_summary')
@component('domain._card')
<form method="POST" action="{{ route('users.update', $managedUser) }}">
    @csrf
    @method('PUT')
    @include('domain.users.form')
    @include('domain._form_actions', ['cancelUrl' => route('users.show', $managedUser)])
</form>
@endcomponent
</div>
