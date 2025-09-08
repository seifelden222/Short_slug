@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <form method="POST" action="{{ route('reset_password') }}">
                @csrf

                <div class="mb-4">
                    <label for="current_password" class="form-label fw-semibold">
                        <i class="bi bi-lock me-2"></i>Current Password</label>
                    <input id="current_password" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                        name="current_password" placeholder="Enter your current password">
                    @error('current_password')
                    <div class="text-danger mt-2"><small><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</small></div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">
                        <i class="bi bi-lock me-2"></i>New Password</label>
                    <input id="password" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                        name="password" placeholder="Enter new password">
                    @error('password')
                    <div class="text-danger mt-2"><small><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</small></div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-semibold">
                        <i class="bi bi-lock me-2"></i>Confirmation Password</label>
                    <input id="password_confirmation" type="password" class="form-control form-control-lg border-0 bg-light rounded-3"
                        name="password_confirmation" placeholder="Confirm new password">
                    @error('password_confirmation')
                    <div class="text-danger mt-2"><small><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</small></div>
                    @enderror
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Reset Password
                    </button>
            </form>
        </div>
    </div>
</div>

@endsection
