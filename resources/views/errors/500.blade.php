@extends('errors.layout')

@section('code', '500')
@section('icon', 'ri-error-warning-line')
@section('icon_bg', 'bg-danger-subtle text-danger')
@section('code_color', 'text-danger')
@section('title', 'Server Error')
@section('message', "Something went wrong on our end. We're already looking into it. Please try again in a moment.")

@section('actions')
  <button onclick="window.location.reload()" class="btn btn-primary">
    <i class="ri-refresh-line me-1"></i> Try Again
  </button>
  <button onclick="history.back()" class="btn btn-outline-secondary">
    <i class="ri-arrow-left-line me-1"></i> Go Back
  </button>
@endsection

@if(auth()->check())
  @section('secondary_actions')
    <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">
      <i class="ri-dashboard-line me-1"></i> Go to Dashboard
    </a>
  @endsection
@endif
