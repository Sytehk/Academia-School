@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-default" data-collapsed="0">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Book') }}
                    </div>
                </div>
                <table class="table table-striped table-bordered" width="100%">
                    <tbody style="text-align: center;">
                        <tr>
                            <td colspan="4">
                                <img src="{{ asset('uploads/images/books/' . $book->photo) }}" style="border-radius: 5px;"
                                    width="160px" alt="">
                            </td>
                        </tr>

                        <tr>
                            <td colspan="4">
                                <img src="{{ asset('uploads/images/books/bar_codes/' . $book->barcode) }}"
                                    style="border-radius: 5px;" width="300px" alt="">
                            </td>
                        </tr>

                        <tr>
                            <td colspan="2">{{ _lang('Name') }}</td>
                            <td colspan="2">{{ $book->book_name }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">{{ _lang('Code') }}</td>
                            <td colspan="2">{{ $book->code }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Publisher') }}</td>
                            <td>{{ $book->publisher }}</td>
                            <td>{{ _lang('Author') }}</td>
                            <td>{{ $book->author }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Total Pages') }}</td>
                            <td>{{ $book->total_page }}</td>
                            <td>{{ _lang('Identification Page') }}</td>
                            <td>{{ $book->identification_page }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Quantity') }}</td>
                            <td>{{ $book->quantity }}</td>
                            <td>{{ _lang('Price') }}</td>
                            <td>{{ $book->price }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Book Self') }}</td>
                            <td>{{ $book->bookself }}</td>
                            <td>{{ _lang('Rack No') }}</td>
                            <td>{{ $book->rack }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Publish Year') }}</td>
                            <td>{{ $book->publish_year }}</td>
                            <td>{{ _lang('Provider') }}</td>
                            <td>{{ $book->provider }}</td>
                        </tr>
                        <tr>
                            <td>{{ _lang('Description') }}</td>
                            <td>{{ $book->description }}</td>
                            <td>{{ _lang('Publish Date') }}</td>
                            <td>{{ $book->publish_date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
