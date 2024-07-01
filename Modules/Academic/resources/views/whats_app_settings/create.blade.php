@extends('layouts.backend')


@section('content')
    <div class="card">


        <div class="card-header">

            <h4>
                Create WhatsApp Setting
            </h4>

        </div>



        <div class="card-body">


            <form method="POST" action="{{ route('whats-app-settings.store') }}">

                @csrf



                <div class="row">


                    <div class="col-md-6 mb-3">

                        <label>
                            Provider
                        </label>


                        <select name="provider" class="form-control">


                            <option value="meta">
                                Meta Official
                            </option>


                            <option value="twilio">
                                Twilio
                            </option>


                        </select>


                    </div>




                    <div class="col-md-6 mb-3">

                        <label>
                            Phone Number ID
                        </label>


                        <input name="phone_number_id" class="form-control">


                    </div>





                    <div class="col-md-12 mb-3">

                        <label>
                            Access Token
                        </label>


                        <textarea name="access_token" class="form-control"></textarea>


                    </div>





                    <div class="col-md-6 mb-3">


                        <label>
                            Business Account ID
                        </label>


                        <input name="business_account_id" class="form-control">


                    </div>





                    <div class="col-md-3 mb-3">

                        <label>
                            Language
                        </label>


                        <input name="language" value="en" class="form-control">


                    </div>




                    <div class="col-md-3 mb-3">


                        <label>
                            Status
                        </label>


                        <select name="status" class="form-control">


                            <option value="1">
                                Enabled
                            </option>


                            <option value="0">
                                Disabled
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
