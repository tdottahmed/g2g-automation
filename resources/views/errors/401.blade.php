@extends('errors.layout')

@section('code', '401')
@section('icon', 'ri-lock-password-line')
@section('icon_bg', 'bg-danger-subtle text-danger')
@section('code_color', 'text-danger')
@section('title', 'Authentication Required')
@section('message', 'You must be signed in to access this resource. Please log in and try again.')

@section('actions')
  <a href="{{ route('login') }}" class="btn btn-primary">
    <i class="ri-login-box-line me-1"></i> Sign In
  </a>
  <button onclick="history.back()" class="btn btn-outline-secondary">
    <i class="ri-arrow-left-line me-1"></i> Go Back
  </button>
@endsection
