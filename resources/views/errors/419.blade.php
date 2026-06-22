@extends('errors.layout')

@section('code', '419')
@section('icon', 'ri-time-line')
@section('icon_bg', 'bg-warning-subtle text-warning')
@section('code_color', 'text-warning')
@section('title', 'Session Expired')
@section('message', 'Your session has timed out for security reasons. Please refresh the page and try again — your work may still be intact.')

@section('actions')
  <button onclick="history.back()" class="btn btn-primary">
    <i class="ri-refresh-line me-1"></i> Go Back & Retry
  </button>
  <a href="{{ route('login') }}" class="btn btn-outline-secondary">
    <i class="ri-login-box-line me-1"></i> Sign In Again
  </a>
@endsection
