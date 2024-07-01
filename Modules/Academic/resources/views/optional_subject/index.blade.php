@extends('layouts.backend')


@section('page_title_breadcrumb')
    {{ _lang('Optional Subject Configuration') }}
@endsection



@section('create-button')
    <a href="{{ route('optional-subject-config.create') }}" class="btn btn-sm fw-bold btn-primary">

        {{ _lang('Add Optional Subject') }}

    </a>
@endsection





@section('content')
    <div class="row">

        <div class="col-lg-12">


            <div class="card card-default">


                <div class="card-header">

                    <div class="card-title">

                        {{ _lang('Optional Subject List') }}

                    </div>

                </div>



                <div class="card-body">


                    <div class="table-responsive">


                        <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">


                            <thead>

                                <tr>

                                    <th>{{ _lang('Name') }}</th>

                                    <th>{{ _lang('Class') }}</th>

                                    <th>{{ _lang('Group') }}</th>

                                    <th>{{ _lang('Subjects') }}</th>

                                    <th>{{ _lang('Limit') }}</th>

                                    <th>{{ _lang('Action') }}</th>

                                </tr>


                            </thead>



                            <tbody>


                                @foreach ($configs as $data)
                                    <tr>


                                        <td>

                                            {{ $data->name }}

                                        </td>



                                        <td>

                                            {{ $data->class->class_name ?? '' }}

                                        </td>



                                        <td>

                                            {{ $data->group->group_name ?? 'All' }}

                                        </td>




                                        <td>


                                            @foreach ($data->items as $item)
                                                <span class="badge bg-primary">


                                                    {{ $item->subject->subject_name }}


                                                </span>
                                            @endforeach


                                        </td>



                                        <td>

                                            {{ $data->selection_limit }}

                                        </td>




                                        <td>


                                            <a href="{{ route('optional-subject-config.edit', $data->id) }}"
                                                class="btn btn-warning btn-sm">


                                                <i class="fa fa-pencil"></i>


                                            </a>





                                            <form action="{{ route('optional-subject-config.destroy', $data->id) }}"
                                                method="POST" style="display:inline">


                                                @csrf

                                                @method('DELETE')


                                                <button type="submit" class="btn btn-danger btn-sm show_confirm">


                                                    <i class="fa fa-trash"></i>


                                                </button>


                                            </form>



                                        </td>


                                    </tr>
                                @endforeach


                            </tbody>


                        </table>


                    </div>



                </div>


            </div>


        </div>


    </div>
@endsection
