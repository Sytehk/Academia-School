@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book Print') }}
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="title">{{ _lang('Library Books code print') }}</h4>
                </div>

                <div class="card-body">
                    <form action="{{ route('books.ber-code-print') }}" method="POST">
                        @csrf

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Books Code') }}</th>
                                    <th>{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="number" class="form-control" name="books_code[]" value="0">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success add-row" type="button">+</button>
                                        <button class="btn btn-sm btn-danger remove-row" type="button">-</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="submit" value="1"
                                    class="btn btn-primary btn-block rect-btn margin_top_24"> <i
                                        class="fa fa-cloud-download" aria-hidden="true"></i>
                                    {{ _lang('Print PDF') }}</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <div class="row">

    </div>
@endsection

@section('js-script')
    <script>
        $(document).ready(function() {
            // Add row
            $(document).on("click", ".add-row", function() {
                var newRow = $(this).closest("tr").clone();
                newRow.find("input[type='number']").val('0');
                $(this).closest("tr").after(newRow);
            });

            // Remove row
            $(document).on("click", ".remove-row", function() {
                if ($("tbody tr").length > 1) {
                    $(this).closest("tr").remove();
                }
            });
        });
    </script>
@endsection
