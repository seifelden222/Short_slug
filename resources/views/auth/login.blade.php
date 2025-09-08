@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card auth-card border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-box-arrow-in-right display-4 text-primary mb-3"></i>
                    <h3 class="fw-bold">Welcome Back</h3>
                    <p class="text-muted">Sign in to your account</p>
                </div>

                @if(session('success'))
                <div class="alert alert-success border-0 rounded-3">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger border-0 rounded-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger border-0 rounded-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0 ms-3">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input id="email" type="email" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input id="password" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="password" required placeholder="Enter your password">
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-muted">Don't have an account?
                            <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Create one here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
