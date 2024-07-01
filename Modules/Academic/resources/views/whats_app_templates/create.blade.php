@extends('layouts.backend')


@section('content')
    <div class="card">


        <div class="card-header">

            <h4>
                Create WhatsApp Template
            </h4>


        </div>



        <div class="card-body">


            <form method="POST" action="{{ route('whats-app-templates.store') }}">


                @csrf



                <div class="row">


                    <div class="col-md-6 mb-3">


                        <label>
                            Template Name
                        </label>


                        <input name="name" class="form-control" value="{{ old('name') }}" placeholder="Fee Reminder">


                    </div>





                    <div class="col-md-6 mb-3">


                        <label>
                            Event
                        </label>


                        <select name="event" class="form-control">


                            <option value="fee_due" @selected(old('event') == 'fee_due')>
                                Fee Due
                            </option>


                            <option value="fee_collected" @selected(old('event') == 'fee_collected')>
                                Fee Collected
                            </option>


                            <option value="absent" @selected(old('event') == 'absent')>
                                Absent
                            </option>


                            <option value="result" @selected(old('event') == 'result')>
                                Result
                            </option>


                            <option value="notice" @selected(old('event') == 'notice')>
                                Notice
                            </option>


                        </select>


                    </div>


                    <div class="col-md-12 mb-3">


                        <label>
                            Message
                        </label>


                        <textarea name="message" rows="6" class="form-control"
                            placeholder="Hello @{{student_name}} Your fee is pending">{{ old('message') }}</textarea>
                        <small class="form-text text-muted">Available placeholders: student_name, student_id, class, section, roll, date, status, exam_name, total_marks, gpa, grade, position, invoice_number, collection_date, amount_paid, due_amount, payment_method, receipt_number, school_name, contact.</small>
                    </div>


                    <div class="col-md-3 mb-3">


                        <label>
                            Status
                        </label>


                        <select name="status" class="form-control">


                            <option value="1">
                                Active
                            </option>


                            <option value="0">
                                Inactive
                            </option>


                        </select>


                    </div>



                </div>





                <button class="btn btn-primary">

                    Save

                </button>


            </form>



        </div>


    </div>
@endsection
