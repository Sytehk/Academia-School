@extends('layouts.backend')

@section('content')
    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <h4 class="mb-0">
                Update WhatsApp Setting
            </h4>


            <a href="{{ route('whats-app-settings.index') }}" class="btn btn-secondary">

                Back

            </a>

        </div>



        <div class="card-body">


            <form method="POST" action="{{ route('whats-app-settings.update', $setting->id) }}">

                @csrf

                @method('PUT')


                <div class="row">


                    {{-- Provider --}}

                    <div class="col-md-6 mb-3">


                        <label class="form-label">
                            Provider
                        </label>


                        <select name="provider" class="form-control">


                            <option value="meta" {{ $setting->provider == 'meta' ? 'selected' : '' }}>

                                Meta Official

                            </option>


                            <option value="twilio" {{ $setting->provider == 'twilio' ? 'selected' : '' }}>

                                Twilio

                            </option>


                        </select>


                    </div>





                    {{-- Phone ID --}}

                    <div class="col-md-6 mb-3">


                        <label class="form-label">
                            Phone Number ID
                        </label>


                        <input type="text" name="phone_number_id" class="form-control"
                            value="{{ old('phone_number_id', $setting->phone_number_id) }}">
                        <small class="text-muted">Meta: WhatsApp Phone Number ID. Twilio: the WhatsApp-enabled sender number (e.g. +14155238886).</small>


                    </div>






                    {{-- Business ID --}}

                    <div class="col-md-6 mb-3">


                        <label class="form-label">
                            Business Account ID
                        </label>


                        <input type="text" name="business_account_id" class="form-control"
                            value="{{ old('business_account_id', $setting->business_account_id) }}">
                        <small class="text-muted">Meta: WhatsApp Business Account ID. Twilio: Account SID.</small>


                    </div>






                    {{-- Language --}}

                    <div class="col-md-3 mb-3">


                        <label class="form-label">
                            Language
                        </label>


                        <input type="text" name="language" class="form-control"
                            value="{{ old('language', $setting->language) }}">


                    </div>







                    {{-- Status --}}

                    <div class="col-md-3 mb-3">


                        <label class="form-label">
                            Status
                        </label>


                        <select name="status" class="form-control">


                            <option value="1" {{ $setting->status == 1 ? 'selected' : '' }}>

                                Enabled

                            </option>



                            <option value="0" {{ $setting->status == 0 ? 'selected' : '' }}>

                                Disabled

                            </option>


                        </select>


                    </div>






                    {{-- Token --}}

                    <div class="col-md-12 mb-3">


                        <label class="form-label">

                            Access Token

                        </label>



                        <textarea name="access_token" rows="5" class="form-control" placeholder="Leave empty to keep existing token">{{ $setting->access_token }}</textarea>



                        <small class="text-muted">

                            Current token will remain unchanged if you leave this empty. Meta: permanent access token. Twilio: Auth Token.

                        </small>


                    </div>




                </div>





                <button class="btn btn-primary">

                    Update Setting

                </button>



            </form>



        </div>


    </div>
@endsection
