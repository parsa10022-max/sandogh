@extends('layouts.app')

@section('title', 'داشبورد')

@section('content')

    <div class="container-fluid dashboard-page">


        @include('dashboard.partials.statistics')
        {{-- دسترسی سریع --}}
        @include('dashboard.partials.quick-links')
        @include('dashboard.partials.action-needed')
  </div>

@endsection
