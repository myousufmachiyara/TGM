@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
  <div class="page-header d-flex justify-content-between">
    <div>
      <h3 class="fw-bold mb-3">Dashboard</h3>
      <h6 class="op-7 mb-2">Free Bootstrap 5 Admin Dashboard</h6>
    </div>
    <div>
      <ul class="breadcrumbs mb-3">
        <li class="nav-home">
          <a href="#">
            <i class="fa fa-home"></i>
          </a>
        </li>
        <li class="separator">
          <i class="fa fa-chevron-right"></i>
        </li>
        <li class="nav-item">
          <a href="#">Purchasing</a>
        </li>
        <li class="separator">
        <i class="fa fa-chevron-right"></i>
        </li>
        <li class="nav-item">
          <a href="#">PO</a>
        </li>
      </ul>
    </div>
  </div>
@endsection