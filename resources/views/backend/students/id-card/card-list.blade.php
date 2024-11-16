@php
    use Carbon\Carbon;

    $school = $settings['school_name'] ?? get_option('school_name');
    $logo = get_logo();
    $address = $settings['address'] ?? null;
    $signature = $principalSignature ? asset('storage/signatures/' . $principalSignature->image) : null;
    $validUntil = $validity_date ? Carbon::parse($validity_date)->format('d M Y') : null;

    $cardTitle = _lang('Student ID Cards');
    $cardCount = count($students);
    $validityNote = $validUntil ? _lang('Valid until') . ' ' . $validUntil : null;
@endphp

@extends('backend.id-cards.layout')

@section('cards')
    @foreach ($students as $student)
        @php
            $fullName = trim($student->first_name . ' ' . $student->last_name) ?: $student->name;

            $role = collect([
                $student->class_name ? _lang('Class') . ' ' . $student->class_name : null,
                $student->section_name,
                $student->group_name,
            ])->filter()->implode(' · ');

            $guardian = $student->father_name ?: $student->mother_name;
        @endphp

        <div class="idc-pair">
            <x-id-card.front :school="$school" :address="$address" :logo="$logo"
                :label="_lang('Student Identity Card')"
                :photo="$student->image ? asset('storage/users/' . $student->image) : null" :name="$fullName"
                :role="$role" :fields="[
                    'Roll' => $student->roll,
                    'Blood' => $student->blood_group,
                    'D.O.B' => $student->birthday ? Carbon::parse($student->birthday)->format('d M Y') : null,
                    'Phone' => $student->phone,
                    'Guardian' => $guardian,
                ]"
                :qr-value="$student->qr_code ?: ($student->registration_no ?: $student->id)"
                id-label="Reg. No" :id-value="$student->registration_no ?: $student->id"
                :valid-until="$validUntil" :signature="$signature" />

            <x-id-card.back :school="$school" :logo="$logo" :address="$address"
                :phone="$settings['phone'] ?? null" :email="$settings['email'] ?? null"
                :website="$settings['website_link'] ?? null"
                :emergency="[
                    'label' => _lang('Emergency Contact') . ($student->information_sent_to_relation ? ' · ' . $student->information_sent_to_relation : ''),
                    'value' => collect([
                        $student->information_sent_to_name ?: $guardian,
                        $student->information_sent_to_phone ?: ($settings['emergency_number'] ?? null),
                    ])->filter()->implode(' · '),
                ]"
                :qr-value="$student->qr_code ?: ($student->registration_no ?: $student->id)" />
        </div>
    @endforeach
@endsection
