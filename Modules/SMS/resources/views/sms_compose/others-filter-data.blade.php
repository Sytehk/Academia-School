<table class="table table-bordered data-table">
    <thead>
        <tr>
            <th scope="col"> <button type="button" onclick="checkAllInput()" class="btn btn-success btn-sm "
                    id="checkAllButton"> <i class="fa fa-check" aria-hidden="true"></i> </button>
            </th>
            <th scope="col">SL</th>
            <th scope="col">User Id</th>
            <th scope="col">Name</th>
            <th scope="col">Phone Number</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $key => $item)
            <tr>
                <td scope="row"><input type="checkbox" value="{{ $item->phone }}" name="users[{{ $item->id }}]"
                        class="user-checkbox" checked="true"></td>
                <td scope="row">{{ $key + 1 }}</td>
                <td scope="row">{{ $item->id }}</td>
                <td>{{ $item->name }} </td>
                <td>{{ $item->phone }}</td>

            </tr>
        @endforeach

    </tbody>
</table>
