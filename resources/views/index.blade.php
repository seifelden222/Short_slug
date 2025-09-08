@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <!-- Welcome Section -->
        <!-- <div class="card auth-card border-0 rounded-4 mb-4">
            <div class="card-body text-center p-5">
                <i class="bi bi-house-heart display-4 text-info mb-3"></i>
                <h2 class="fw-bold mb-3">Welcome Back!</h2>
                <p class="text-muted fs-5">Hello, <span class="fw-semibold text-primary">{{ Auth::user()->name }}</span></p>
                <p class="text-muted">Manage your profile settings below</p>
            </div>
        </div> -->

        <!-- Profile Update Form -->
        <div class="card auth-card border-0 rounded-4">
            <div class="card-header bg-transparent border-0 p-4 pb-0">
                <h4 class="fw-bold mb-0">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Profile Settings
                </h4>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                <div class="alert alert-success border-0 rounded-3 mb-4">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger border-0 rounded-3 mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                </div>
                @endif

                <form action="{{ route('update') }}" method="post">
                    @csrf
                    @method('PATCH')

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bi bi-person me-2"></i>Full Name
                            </label>
                            <input id="name" type="text" class="form-control form-control-lg border-0 bg-light rounded-3"
                                   name="name" value="{{ Auth::user()->name }}" autofocus>
                            @error('name')
                            <div class="text-danger mt-2"><small><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</small></div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </label>
                            <input id="email" type="email" class="form-control form-control-lg border-0 bg-light rounded-3"
                                   name="email" value="{{ Auth::user()->email }}">
                            @error('email')
                            <div class="text-danger mt-2"><small><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</small></div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-semibold mb-3">
                        <i class="bi bi-shield-lock me-2 text-warning"></i>Change Email
                    </h5>

                    <div class="mb-4">
                        <label for="current_password" class="form-label fw-semibold">
                            <i class="bi bi-key me-2"></i>Current Password
                        </label>
                        <input id="current_password" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                               name="current_password" placeholder="Enter your current password">
                        @error('current_password')
                        <div class="text-danger mt-2"><small><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</small></div>
                        @enderror
                    </div>



                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                            <i class="bi bi-check-circle me-2"></i>Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
