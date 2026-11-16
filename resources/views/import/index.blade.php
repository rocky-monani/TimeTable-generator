@extends('layouts.app')

@section('title','Excel Import')

@section('content')

    <div class="d-flex align-items-center justify-content-between mb-4 mt-2">
        <div>
            <h2 class="h3 font-weight-bold text-dark mb-0"><i class="fas fa-file-excel text-success mr-2"></i> Import Excel Data</h2>
            <p class="text-muted mb-0">Upload master data for departments, subjects, faculty, and rooms.</p>
        </div>
        <a href="/excel-template" class="btn btn-success shadow-sm"><i class="fas fa-download mr-2"></i>Download Template</a>
    </div>

    <div class="card shadow-sm border-0">

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="/excel-import" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label>Select Excel File</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <br>

            <button class="btn btn-primary">
                Upload Excel
            </button>
        </form>

    </div>

</div>

@endsection