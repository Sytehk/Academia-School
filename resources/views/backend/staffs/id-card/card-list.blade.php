@php
    use Carbon\Carbon;

    $school = $settings['school_name'] ?? get_option('school_name');
    $logo = get_logo();
    $address = $settings['address'] ?? null;
    $signature = $principalSignature ? asset('storage/signatures/' . $principalSignature->image) : null;

    $cardTitle = _lang('Staff ID Cards');
    $cardCount = count($staffs);
@endphp

@extends('backend.id-cards.layout')

@section('cards')
    @foreach ($staffs as $staff)
        @php
            // staffs.name is the card name of record; fall back to the linked user
            // account so a card is never printed blank.
            $fullName = $staff->name ?: $staff->user_name;
            $employeeId = $staff->access_key ?: $staff->id;
        @endphp

        <div class="idc-pair">
            <x-id-card.front :school="$school" :address="$address" :logo="$logo"
                :label="_lang('Staff Identity Card')"
                :photo="$staff->user_image ? asset('storage/users/' . $staff->user_image) : null" :name="$fullName"
                :role="$staff->designation ?: _lang('Staff')" :fields="[
                    'Department' => $staff->department_name,
                    'Blood' => $staff->blood,
                    'D.O.B' => $staff->birthday ? Carbon::parse($staff->birthday)->format('d M Y') : null,
                    'Phone' => $staff->phone,
                    'Joined' => $staff->joining_date ? Carbon::parse($staff->joining_date)->format('d M Y') : null,
                ]"
                :qr-value="$employeeId" id-label="Emp. ID" :id-value="$employeeId" :signature="$signature" />

            <x-id-card.back :school="$school" :logo="$logo" :address="$address"
                :phone="$settings['phone'] ?? null" :email="$settings['email'] ?? null"
                :website="$settings['website_link'] ?? null"
                :emergency="$settings['emergency_number'] ?? null" :qr-value="$employeeId" />
        </div>
    @endforeach
@endsection
