@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Zoom Meetings') }}
@endsection


@section('content')
    <div class="card">

        <div class="card-body">


            <div class="table-responsive">


                <table class="table table-bordered table-striped align-middle">


                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Topic</th>

                            <th>Teacher</th>

                            <th>Class</th>

                            <th>Section</th>

                            <th>Start Time</th>

                            <th>Duration</th>

                            <th>Join</th>

                        </tr>

                    </thead>



                    <tbody>


                        @forelse($meetings as $key=>$meeting)
                            <tr>


                                <td>
                                    {{ $key + 1 }}
                                </td>


                                <td>

                                    {{ $meeting->topic }}

                                    @if ($meeting->agenda)
                                        <br>

                                        <small class="text-muted">
                                            {{ $meeting->agenda }}
                                        </small>
                                    @endif

                                </td>



                                <td>

                                    {{ $meeting->teacher?->name ?? 'N/A' }}

                                </td>



                                <td>

                                    {{ $meeting->class?->class_name ?? 'N/A' }}

                                </td>



                                <td>

                                    {{ $meeting->section?->section_name ?? 'N/A' }}

                                </td>



                                <td>

                                    {{ date('d M Y h:i A', strtotime($meeting->start_time)) }}

                                </td>



                                <td>

                                    {{ $meeting->duration }} Min

                                </td>



                                <td>


                                    @if ($meeting->join_url)
                                        <a href="{{ $meeting->join_url }}" target="_blank" class="btn btn-sm btn-success">

                                            Join Meeting

                                        </a>
                                    @else
                                        <span class="text-danger">
                                            No Link
                                        </span>
                                    @endif


                                </td>



                            </tr>


                        @empty


                            <tr>

                                <td colspan="8" class="text-center">

                                    No Zoom Meeting Available

                                </td>

                            </tr>
                        @endforelse



                    </tbody>


                </table>


            </div>


        </div>


    </div>
@endsection
