@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Book Issue') }}
@endsection

<style>
    .card-body {
        padding: 20px;
    }

    .member-info li {
        margin-bottom: 10px;
    }

    .member-info span {
        font-weight: bold;
    }
</style>

@section('content')
    <form action="{{ route('multiple-book-issue.store') }}" method="post">
        @csrf

        <div class="card">
            <div class="row">
                <div class="my-5 col-lg-12">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="content no-export">
                                <label class="my-2 control-label">{{ _lang('Books Search') }}</label>
                                <select name="book_id" class="form-control select2 BookCode">

                                    @foreach ($books as $book)
                                        <option value="{{ $book->code }}"
                                            {{ $book->id == old('book_id') ? 'selected' : '' }}>
                                            {{ $book->book_name }} -
                                            {{ $book->code }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="content no-export">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('Books Search') }}</label>
                                    <input type="number" class="form-control BookCode" name="" value=""
                                        placeholder="Enter Book code / SKU / Scan bar code" pattern="[0-9]*" max="999999">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="my-5 col-lg-12 table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 book-search-table">
                        <thead>
                            <th>{{ _lang('SL') }}</th>
                            <th>{{ _lang('Name') }}</th>
                            <th>{{ _lang('Code') }}</th>
                            <th>{{ _lang('Writer') }}</th>
                            <th>{{ _lang('Quantity') }}</th>
                            <th>{{ _lang('Return Date') }}</th>
                            <th>{{ _lang('Action') }}</th>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="my-5 col-lg-12">


                    <div class="row">
                        <div class="col-lg-6">
                            <div class="content no-export">
                                <label class="my-2 control-label">{{ _lang('Member Search') }}</label>
                                <select name="" class="form-control libraryId select2">

                                    @foreach ($library_members->unique('name') as $member)
                                        <option value="{{ $member->library_id }}"
                                            {{ $member->library_id == old('library_id') ? 'selected' : '' }}>
                                            {{ $member->library_id }} -
                                            {{ $member->name }} </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="content no-export">
                                <div class="form-group">
                                    <label class="my-2 control-label">{{ _lang('Member Search') }}</label>
                                    <input type="number" class="form-control libraryId" name="" value=""
                                        placeholder="Enter Book code / SKU / Scan bar code" pattern="[0-9]*">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 card-body-2" style="display: none">
                        <div class="row">
                            <div class="col-lg-6">
                                <img src="" alt="" style="width: 150px; height: 150px;" id="img_url">
                            </div>
                            <div class="col-lg-6">
                                <ul class="member-info">
                                    <li>
                                        <span>Library ID:</span>
                                        <input type="text" name="search_library_id" id="search_library_id" value=""
                                            style="border: none" readonly>
                                    </li>
                                    <li>
                                        <span>Name:</span>
                                        <span id="member_name"></span>
                                    </li>

                                    <li>
                                        <span>Occupation:</span>
                                        <span id="member_occupation"></span>
                                    </li>

                                    <li>
                                        <span>Phone:</span>
                                        <span id="member_phone"></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="ml-5 form-group">
                                    <button type="submit" class="mt-5 btn btn-success">{{ _lang('Book Issue') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </form>
@endsection

@section('js-script')
    {{-- Books script code start --}}
    <script>
        $(document).ready(function() {
            // Function to perform AJAX request
            function searchBook(searchValue) {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('book.search') }}",
                    data: {
                        _token: '{{ csrf_token() }}',
                        search_books: searchValue
                    },
                    success: function(data) {
                        if (data.searchBook) {
                            addBookToTable(data.searchBook);
                        }
                    },
                    error: function(error) {
                        console.error(error);
                    }
                });
            }

            // Bind event listener for select element
            $('select.BookCode').on('change', function() {
                var selectedValue = $(this).val().trim();
                if (selectedValue !== '') {
                    searchBook(selectedValue);
                }
            });

            // Bind event listener for input element with class BookCode
            $('input.BookCode').on('input', function() {
                var inputValue = $(this).val().trim();
                if (inputValue !== '') {
                    searchBook(inputValue);
                }
            });


            // Function to add book to table row
            function addBookToTable(book) {
                // Check if the book already exists in the table
                var existingRow = $('.book-search-table tbody tr[data-id="' + book.id + '"]');
                if (!existingRow.length) {
                    // Calculate return date 14 days from today
                    var today = new Date();
                    var returnDate = new Date(today);
                    returnDate.setDate(returnDate.getDate() + 14);

                    // Format return date as YYYY-MM-DD
                    var formattedReturnDate = returnDate.toISOString().split('T')[0];

                    // Add new row if book doesn't exist
                    var newRow = '<tr data-id="' + book.id + '">' +
                        '<input type="hidden" name="book_ids[]" value="' + book.id + '">' +
                        '<td>' + book.id + '</td>' +
                        '<td>' + book.book_name + '</td>' +
                        '<td>' + book.code + '</td>' +
                        '<td>' + book.author + '</td>';

                    // Check if the quantity is 0
                    if (book.quantity == 0) {
                        // If quantity is 0, show "Stock Out" with danger red mark
                        newRow += '<td><span class="text-danger">Stock Out</span></td>';
                    } else {
                        // If quantity is not 0, show the quantity
                        newRow += '<td>' + book.quantity + '</td>';
                    }

                    // Add the return date input field
                    newRow +=
                        '<td><input type="date" name="return_dates[]" class="form-control return-date" value="' +
                        formattedReturnDate + '"></td>' +
                        '<td><button type="button" class="btn btn-danger btn-sm delete-book">Delete</button></td>' +
                        '</tr>';

                    $('.book-search-table tbody').append(newRow);
                }
            }

            // Handle delete book button click
            $(document).on('click', '.delete-book', function() {
                $(this).closest('tr').remove();
            });
        });

        // Bind event listener for input element with class BookCode
        $('input.BookCode').on('input', function() {
            var inputValue = $(this).val().trim();
            if (inputValue.length > 6) {
                // Trim the input value to 6 digits
                $(this).val(inputValue.slice(0, 6));
            }
        });
    </script>
    {{-- Books script code end --}}


    <script>
        $(document).ready(function() {
            // Bind event listener for select element
            $('select.libraryId').on('change', function() {
                var selectedValue = $(this).val().trim();
                performAjaxRequest(selectedValue);
            });

            // Bind event listener for input element
            $('input.libraryId[type="number"]').on('input', function() {
                var inputValue = $(this).val().trim();
                performAjaxRequest(inputValue);
            });

            // Function to perform AJAX request
            function performAjaxRequest(searchValue) {
                console.log(searchValue);
                if (searchValue !== '') {
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('member.search') }}",
                        data: {
                            _token: '{{ csrf_token() }}',
                            search_member: searchValue
                        },
                        success: function(data) {
                            if (data.library_member) {
                                var imageName = data.library_member.image;
                                var imagePath = "{{ asset('storage/users/') }}/" + imageName;
                                $('#img_url').attr('src', imagePath);
                                $('#member_name').text(data.library_member.name);
                                $('#member_occupation').text(data.library_member.member_type);
                                $('#member_phone').text(data.library_member.phone);
                                $('#search_library_id').val(data.library_member.library_id);
                                $('.card-body-2').show(); // Display the card body
                            } else {
                                // Handle the case where no member is found
                                $('#member_name').text('Member not found');
                                $('#member_occupation').text('');
                                $('#member_phone').text('');
                                $('.card-body-2').hide(); // Hide the card body if no member found
                            }
                        },
                        error: function(error) {
                            console.error(error);
                        }
                    });
                } else {
                    // Clear the displayed member info if no member is selected
                    $('#member_name').text('');
                    $('#member_occupation').text('');
                    $('#member_phone').text('');
                    $('.card-body-2').hide(); // Hide the card body if search value is empty
                }
            }
        });
    </script>
@endsection
