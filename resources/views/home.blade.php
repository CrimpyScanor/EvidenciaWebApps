@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">{{ __('Panel de Control (Dashboard)') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4 class="mb-3">¡Welcome to the sistem!</h4>
                    <p class="text-muted">This view is private and only the users had loggin in the page </p>
                    <hr>
                    <p class="mb-0"><strong>User:</strong> {{ Auth::user()->name }} ({{ Auth::user()->email }})</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection