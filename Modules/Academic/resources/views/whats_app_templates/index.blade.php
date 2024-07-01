@extends('layouts.backend')


@section('content')
    <div class="card">


        <div class="card-header d-flex justify-content-between">


            <h4>
                WhatsApp Templates
            </h4>


            <a href="{{ route('whats-app-templates.create') }}" class="btn btn-primary">

                Add Template

            </a>


        </div>




        <div class="card-body">


            <table class="table table-bordered">


                <thead>

                    <tr>

                        <th>Name</th>

                        <th>Event</th>

                        <th>Message</th>

                        <th>Status</th>

                        <th width="150">
                            Action
                        </th>


                    </tr>

                </thead>



                <tbody>


                    @foreach ($templates as $item)
                        <tr>


                            <td>
                                {{ $item->name }}
                            </td>



                            <td>

                                <span class="badge bg-info">

                                    {{ $item->event }}

                                </span>

                            </td>




                            <td>

                                {{ Str::limit($item->message, 50) }}

                            </td>





                            <td>


                                @if ($item->status)
                                    <span class="badge bg-success">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        Inactive
                                    </span>
                                @endif


                            </td>





                            <td>


                                <a href="{{ route('whats-app-templates.edit', $item->id) }}" class="btn btn-warning btn-sm">

                                    Edit

                                </a>





                                <form action="{{ route('whats-app-templates.destroy', $item->id) }}" method="POST"
                                    class="d-inline">


                                    @csrf

                                    @method('DELETE')


                                    <button onclick="return confirm('Delete template?')" class="btn btn-danger btn-sm">

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
@endsection
