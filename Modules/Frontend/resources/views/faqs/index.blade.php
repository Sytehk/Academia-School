@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('FAQs') }}
@endsection

@section('create-button')
    <a href="{{ route('faqs.create') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Add New FAQ') }}
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ _lang('Question') }}</th>
                            <th>{{ _lang('Answer') }}</th>
                            <th>{{ _lang('Status') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($faqQuestions as $faq)
                            <tr>
                                <td>{{ $faq->id }}</td>

                                <td>{{ $faq->question }}</td>

                                <td>{{ Str::limit($faq->answer, 40) }}</td>

                                <td>
                                    @if ($faq->status == 1)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td>
                                    <form action="{{ route('faqs.destroy', $faq->id) }}" method="POST">
                                        <a href="{{ route('faqs.edit', $faq->id) }}" class="btn btn-warning btn-sm">
                                            Edit
                                        </a>

                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm">
                                            Delete
                                        </button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>
@endsection
