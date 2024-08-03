@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Add Leave Request') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('hostel-leaves.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ _lang('Student') }}</label>
                        <select name="room_member_id" class="form-control" required>
                            <option value="">{{ _lang('Select Student') }}</option>
                            @foreach ($roomMembers as $member)
                                <option value="{{ $member->id }}">{{ $member->student?->user?->name ?? ('#'.$member->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Leave Start') }}</label>
                        <input type="date" name="leave_start" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">{{ _lang('Leave End') }}</label>
                        <input type="date" name="leave_end" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">{{ _lang('Reason') }}</label>
                        <textarea name="reason" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <button class="btn btn-primary mt-4">{{ _lang('Save') }}</button>
            </form>
        </div>
    </div>
@endsection
