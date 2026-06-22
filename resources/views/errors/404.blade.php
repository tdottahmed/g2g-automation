@extends('errors.layout')

@section('code', '404')
@section('icon', 'ri-compass-discover-line')
@section('icon_bg', 'bg-info-subtle text-info')
@section('code_color', 'text-info')
@section('title', 'Page Not Found')
@section('message', "The page you're looking for doesn't exist or has been moved. Double-check the URL, or head back to a safe place.")

@section('actions')
  @if(auth()->check())
    <a href="{{ route('dashboard') }}" class="btn btn-primary">
      <i class="ri-dashboard-line me-1"></i> Go to Dashboard
    </a>
  @else
    <a href="{{ route('login') }}" class="btn btn-primary">
      <i class="ri-login-box-line me-1"></i> Sign In
    </a>
  @endif
  <button onclick="history.back()" class="btn btn-outline-secondary">
    <i class="ri-arrow-left-line me-1"></i> Go Back
  </button>
@endsection
