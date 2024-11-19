@extends('themes.theme-one.layouts.app')

@section('content')
    @include('themes.theme-one.sections.hero')
    @include('themes.theme-one.sections.about')
    {{-- @include('themes.theme-one.sections.courses') --}}
    @include('themes.theme-one.sections.stats')
    @include('themes.theme-one.sections.teachers')
    @include('themes.theme-one.sections.staffs')
    @include('themes.theme-one.sections.events')
    @include('themes.theme-one.sections.testimonials')
    @include('themes.theme-one.sections.faq')
@endsection
