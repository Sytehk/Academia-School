@extends('layouts.backend')
@section('styles')
    <style>
        /* Styles for the modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;

            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            max-width: 550px;
            width: 100%;
            margin: 0 auto;
            transform: translate(-50%, -50%);
            background-color: #fefefe;
            padding: 20px;
        }

        .close_div {
            text-align: right;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span class="panel-title">
                        {{ _lang('Feedback') }}
                    </span>
                </div>
                <div class="panel-body">
                    <form id="search_form" action="{{ url('student/feedback') }}" method="get">
                        @csrf
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('User Type') }}</label>
                                <select name="user_type" class="form-control" required>
                                    <option value="teacher">Teacher</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <button type="submit" style="margin-top:24px;"
                                    class="btn btn-primary btn-block rect-btn">{{ _lang('Search') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="panel panel-default mt-5">
                        <div class="panel-body">
                            <table class="table table-bordered">
                                <thead>
                                    <th>{{ _lang('Student/Staff Name') }}</th>
                                    <th>{{ _lang('Feedback') }}</th>
                                </thead>
                                <tbody>
                                    @if (isset($users))
                                        @foreach ($users as $data)
                                            <tr>
                                                <td>{{ $data->name }}</td>
                                                <td>
                                                    <input type="hidden" value="{{ $loop->iteration }}" class="dynamic_id">
                                                    <button class="btn btn-primary mb-3 notification_button"
                                                        data-username="{{ $data->id }}">
                                                        <i class="fa fa-envelope"></i>
                                                        Feedback
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-danger">{{ _lang('No Students Found') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal" id="myModal">
        <div class="modal-content">
            <div class="close_div">
                <span class="close">&times;</span>
            </div>
            {{-- <p id="modalUsername"></p> --}}
            <form action="{{ url('feedback/send') }}" method="post">
                @csrf
                <div class="form-group">
                    <input type="hidden" class="form-control" name="staff" value="" id="modalUsername">
                </div>
                <div class="form-group">
                    <label for="exampleFormControlTextarea1">Feedback</label>
                    <textarea class="form-control" name="feedback" id="exampleFormControlTextarea1" rows="3"></textarea>
                </div>
                <button class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
@endsection
@section('js-script')
    <script>
        $(document).ready(function() {
            // Handle button click
            $(".notification_button").click(function() {
                var username = $(this).data("username");
                $("#modalUsername").val(username);

                // Display the modal
                $("#myModal").css("display", "block");
            });

            // Close the modal when the close button is clicked
            $(".close").click(function() {
                $("#myModal").css("display", "none");
            });

            // Close the modal if the user clicks outside of it
            $(window).click(function(event) {
                if (event.target.id === "myModal") {
                    $("#myModal").css("display", "none");
                }
            });
        });
    </script>
@endsection
