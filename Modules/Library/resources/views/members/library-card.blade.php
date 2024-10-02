@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Library Card') }}
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card card-default">
                <div class="card-header">
                    <span class="card-title">
                        {{ _lang('Library Card') }}
                    </span>
                    <button type="button" style="margin-top:-4px;" class="btn btn-primary print pull-right"
                        data-print="library_card">{{ _lang('Print Card') }}</button>
                </div>
                <div class="">
                    <div id="library_card">
                        <div class="card_header">
                            <h5>{{ get_option('school_name') }}</h5>
                            <p>{{ _lang('Library Card') }}</p>
                        </div>
                        <div class="image">
                            <img src="{{ asset('uploads/images/' . $library->image) }}">

                        </div>
                        <div class="library-card">
                            <table>
                                <tr>
                                    <td class="lbl">{{ _lang('Member Type') }}</td>
                                </tr>
                                <td>{{ $library->member_type }}</td>
                                </tr>
                                <tr>
                                    <td class="lbl">{{ _lang('Library ID') }}</td>
                                </tr>
                                <td>{{ $library->library_id }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="card_footer">
                            <p style="font-size: 12px; margin:0; padding:0; margin-top: 5px;">{{ $library->name }}</p>
                            <p style="font-size: 10px !important; margin:0; padding:0;  margin-top: 5px;">
                                {{ _lang('Emergency Contact') }} :
                                {{ get_option('phone') }}</p>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
