@extends('layouts.app')
@section('content')
    <h2 class="text-center">Index Page</h2>
    <p class="text-center">Welcome to the Index Page!</p>

    <form action="{{ route('logout') }}" method="post">
        @csrf
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Logout</button>
        </div>
    </form>
@endsection
