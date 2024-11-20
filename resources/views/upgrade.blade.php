@extends('install.layouts.master')

@section('title', _lang('Upgrade System'))

@section('container')
    <div class="card shadow border-0 rounded">
        <div class="card-header bg-primary text-white px-4">
            <h4 class="px-2">{{ _lang('Upload Upgrade Package') }}</h4>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @elseif(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ url('/upgrade') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group mb-3">
                    <label for="upgrade_zip">{{ _lang('Select Zip File') }}</label>
                    <input type="file" class="form-control" id="upgrade_zip" name="upgrade_zip" required>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-upload"></i> {{ _lang('Upload & Apply Upgrade') }}
                    </button>
                </div>
            </form>

        </div>
    </div>
@endsection
