@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card auth-card border-0 rounded-4">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-plus display-4 text-success mb-3"></i>
                    <h3 class="fw-bold">Create Account</h3>
                    <p class="text-muted">Join us today and get started</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 rounded-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <ul class="mb-0 ms-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-person me-2"></i>Full Name
                        </label>
                        <input id="name" type="text" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="name" value="{{ old('name') }}" autofocus placeholder="Enter your full name" required>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input id="email" type="email" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input id="password" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="password" placeholder="Choose a strong password" required>
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label fw-semibold">
                            <i class="bi bi-lock-fill me-2"></i>Confirm Password
                        </label>
                        <input id="password_confirmation" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="password_confirmation" placeholder="Confirm your password" required>
                    </div>

                    <div class="d-grid mb-4">
                        <button type="submit" class="btn btn-success btn-lg rounded-3 fw-semibold">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-muted">Already have an account?
                            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign in here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
