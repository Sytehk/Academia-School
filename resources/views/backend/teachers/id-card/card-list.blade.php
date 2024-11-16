@php
    use Carbon\Carbon;

    $school = $settings['school_name'] ?? get_option('school_name');
    $logo = get_logo();
    $address = $settings['address'] ?? null;
    $signature = $principalSignature ? asset('storage/signatures/' . $principalSignature->image) : null;

    $cardTitle = _lang('Teacher ID Cards');
    $cardCount = count($teachers);
@endphp

@extends('backend.id-cards.layout')

@section('cards')
    @foreach ($teachers as $teacher)
        @php
            // teachers.name is the card name of record; fall back to the linked user
            // account so a card is never printed blank.
            $fullName = $teacher->name ?: $teacher->user_name;
            $employeeId = $teacher->access_key ?: $teacher->id;
        @endphp

        <div class="idc-pair">
            <x-id-card.front :school="$school" :address="$address" :logo="$logo"
                :label="_lang('Teacher Identity Card')"
                :photo="$teacher->user_image ? asset('storage/users/' . $teacher->user_image) : null"
                :name="$fullName" :role="$teacher->designation ?: _lang('Teacher')" :fields="[
                    'Department' => $teacher->department_name,
                    'Blood' => $teacher->blood,
                    'D.O.B' => $teacher->birthday ? Carbon::parse($teacher->birthday)->format('d M Y') : null,
                    'Phone' => $teacher->phone,
                    'Joined' => $teacher->joining_date ? Carbon::parse($teacher->joining_date)->format('d M Y') : null,
                ]"
                :qr-value="$employeeId" id-label="Emp. ID" :id-value="$employeeId"
                :signature="$signature" />

            <x-id-card.back :school="$school" :logo="$logo" :address="$address"
                :phone="$settings['phone'] ?? null" :email="$settings['email'] ?? null"
                :website="$settings['website_link'] ?? null"
                :emergency="$settings['emergency_number'] ?? null" :qr-value="$employeeId" />
        </div>
    @endforeach
@endsection
