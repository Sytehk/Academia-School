@extends('examination::results.print_layout')
@php $hideToolbar = true; @endphp
@section('content')
    @include('examination::results.student_marks')
@endsection
