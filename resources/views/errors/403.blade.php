@extends('errors.layout')

@section('code', '403')
@section('icon', 'ri-shield-keyhole-line')

@if(auth()->check())
  {{-- Logged in but lacks permission --}}
  @section('icon_bg', 'bg-warning-subtle text-warning')
  @section('code_color', 'text-warning')
  @section('title', 'Access Denied')
  @section('message', "You don't have permission to view this page. If you believe this is a mistake, please contact your administrator.")

  @section('actions')
    <a href="{{ route('dashboard') }}" class="btn btn-primary">
      <i class="ri-dashboard-line me-1"></i> Go to Dashboard
    </a>
    <button onclick="history.back()" class="btn btn-outline-secondary">
      <i class="ri-arrow-left-line me-1"></i> Go Back
    </button>
  @endsection
@else
  {{-- Not logged in --}}
  @section('icon_bg', 'bg-danger-subtle text-danger')
  @section('code_color', 'text-danger')
  @section('title', 'Login Required')
  @section('message', 'You need to be signed in to access this page. Please log in with your credentials to continue.')

  @section('actions')
    <a href="{{ route('login') }}" class="btn btn-primary">
      <i class="ri-login-box-line me-1"></i> Sign In
    </a>
    <button onclick="history.back()" class="btn btn-outline-secondary">
      <i class="ri-arrow-left-line me-1"></i> Go Back
    </button>
  @endsection
@endif
