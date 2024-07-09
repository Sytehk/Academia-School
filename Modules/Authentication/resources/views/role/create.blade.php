@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Role with Permissions') }}
@endsection

@section('create-button')
    <a href="{{ route('roles.index') }}" class="btn btn-primary btn-sm fw-bold">
        {{ _lang('Role List') }}
    </a>
@endsection


@section('content')
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf

        <div class="container-fluid">


            {{-- ROLE CARD --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Role Name</label>
                            <input type="text" name="name" class="form-control form-control-lg"
                                placeholder="Enter role name" value="{{ old('name') }}" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <input type="text" name="description" class="form-control form-control-lg"
                                placeholder="Role description">
                        </div>

                    </div>

                </div>
            </div>



            {{-- PERMISSION SECTION --}}
            <div class="card shadow-sm border-0">

                {{-- HEADER --}}
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">
                        <i class="fa fa-key me-2"></i> Assign Permissions
                    </h5>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="select_all" onclick="selectAll(this)">
                        <label class="form-check-label text-white">Select All</label>
                    </div>

                </div>


                <div class="card-body bg-light">


                    <div class="row g-3">


                        @foreach ($groupedPermissions as $group => $permissions)
                            <div class="col-lg-4 col-md-6">


                                <div class="card border-0 shadow-sm h-100">


                                    {{-- GROUP HEADER --}}
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"
                                        style="padding: 10px !important;">

                                        <span class="fw-bold text-capitalize">
                                            {{ ucwords(str_replace('_', ' ', $group)) }}
                                        </span>

                                        <div class="form-check form-switch m-0">
                                            <input type="checkbox" class="form-check-input group_checkbox"
                                                onclick="selectGroup('{{ $group }}',this)">
                                        </div>

                                    </div>



                                    {{-- PERMISSIONS --}}
                                    <div class="card-body p-3">

                                        @foreach ($permissions as $permission)
                                            <div class="form-check d-flex align-items-center mb-2">

                                                <input type="checkbox" class="form-check-input me-2 permission"
                                                    name="permissions[]" value="{{ $permission->id }}"
                                                    data-group="{{ $group }}" id="permission_{{ $permission->id }}">

                                                <label class="form-check-label small text-dark"
                                                    for="permission_{{ $permission->id }}">

                                                    @if (str_contains($permission->name, '.'))
                                                        {{ ucwords(str_replace('_', ' ', explode('.', $permission->name)[1])) }}
                                                    @else
                                                        {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                                    @endif

                                                </label>

                                            </div>
                                        @endforeach

                                    </div>


                                </div>

                            </div>
                        @endforeach


                    </div>


                </div>


            </div>



            {{-- SUBMIT --}}
            <div class="text-center mt-4">

                @can('role_create')
                    <button type="submit" class="btn btn-success btn-lg px-5">
                        <i class="fa fa-save me-2"></i> Save Role
                    </button>
                @endcan

            </div>



        </div>

    </form>
@endsection



@section('scripts')
    <script>
        // Select all
        function selectAll(el) {
            document.querySelectorAll('.permission').forEach(i => i.checked = el.checked);
            document.querySelectorAll('.group_checkbox').forEach(i => i.checked = el.checked);
        }


        // Select group
        function selectGroup(group, el) {
            document.querySelectorAll(`.permission[data-group="${group}"]`)
                .forEach(i => i.checked = el.checked);
        }


        // Auto sync group checkbox
        document.querySelectorAll('.permission').forEach(item => {

            item.addEventListener('change', function() {

                let group = this.dataset.group;

                let all = document.querySelectorAll(`.permission[data-group="${group}"]`);
                let checked = document.querySelectorAll(`.permission[data-group="${group}"]:checked`);

                let groupCheckbox = this.closest('.card')
                    .querySelector('.group_checkbox');

                if (groupCheckbox) {
                    groupCheckbox.checked = all.length === checked.length;
                }

            });

        });
    </script>
@endsection
