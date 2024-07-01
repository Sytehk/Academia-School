@extends('layouts.backend')


@section('content')
    <div class="card">


        <div class="card-header d-flex justify-content-between">


            <h4>
                WhatsApp Settings
            </h4>


            <div>
                <form action="{{ route('whats-app-settings.test-connection') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        Test Connection
                    </button>
                </form>

                <a href="{{ route('whats-app-settings.create') }}" class="btn btn-primary">

                    Add Setting

                </a>
            </div>


        </div>



        <div class="card-body">


            <table class="table table-bordered">
                <tr>
                    <th>Provider</th>
                    <th>Phone ID</th>
                    <th>Business ID</th>
                    <th>Access Key</th>
                    <th>Language</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                @foreach ($whatsAppSettings as $item)
                    <tr>
                        <td>
                            {{ $item->provider }}
                        </td>

                        <td>
                            {{ $item->phone_number_id }}
                        </td>

                        <td>
                            {{ $item->business_account_id }}
                        </td>

                        <td>
                            {{ Str::limit($item->access_token, 20) }}
                        </td>

                        <td>
                            {{ $item->language }}
                        </td>

                        <td>

                            @if ($item->status)
                                <span class="badge bg-success">
                                    Enabled
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Disabled
                                </span>
                            @endif


                        </td>




                        <td>


                            <a href="{{ route('whats-app-settings.edit', $item->id) }}" class="btn btn-sm btn-warning">

                                Edit

                            </a>




                            <form action="{{ route('whats-app-settings.destroy', $item->id) }}" method="POST"
                                class="d-inline">


                                @csrf

                                @method('DELETE')


                                <button onclick="return confirm('Delete?')" class="btn btn-sm btn-danger">

                                    Delete

                                </button>


                            </form>


                        </td>



                    </tr>
                @endforeach



            </table>


        </div>


    </div>
@endsection
