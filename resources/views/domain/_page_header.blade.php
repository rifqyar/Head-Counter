@php
    $inferredBackUrl = $backUrl ?? collect($breadcrumbs ?? [])->filter()->last();
@endphp
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor">{{ $title }}</h3>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="spa_route">Home</a></li>
            @foreach (($breadcrumbs ?? []) as $label => $url)
                @if ($url)
                    <li class="breadcrumb-item"><a href="{{ $url }}" class="spa_route">{{ $label }}</a></li>
                @else
                    <li class="breadcrumb-item active">{{ is_int($label) ? $url : $label }}</li>
                @endif
            @endforeach
        </ol>
    </div>
    <div class="col-md-7 col-12 align-self-center text-md-right mt-2 mt-md-0">
        @if (($showBack ?? true) && $inferredBackUrl)
            <button type="button" class="btn btn-outline-secondary js-spa-back mr-1" data-fallback-url="{{ $inferredBackUrl }}">
                <i class="mdi mdi-arrow-left"></i>
                Back
            </button>
        @endif
        @isset($actions)
            {{ $actions }}
        @endisset
    </div>
</div>
