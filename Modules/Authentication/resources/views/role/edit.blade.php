@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Role with Permissions') }}
@endsection

@section('create-button')
    <a href="{{ route('roles.index') }}" class="btn btn-sm fw-bold btn-primary">
        {{ _lang('Role List') }}
    </a>
@endsection
@section('content')
    <!-- Card Section -->
    <form action="{{ route('roles.update', $role->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Card -->
        <div class="card shadow-sm bg-white dark:bg-dark text-dark dark:text-light">
            <div class="p-4 pt-0 sm:pt-0 sm:p-7">
                <!-- Grid -->
                <div class="mb-4">
                    <!-- Role Name Input -->
                    <div class="5">
                        <label for="role-name" class="form-label">{{ ucfirst('role') }} Name</label>
                        <input type="text" name="name" id="role-name" required class="form-control"
                            placeholder="Enter role name" value="{{ $role->name }}">
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- Description Input -->
                    <div class="5">
                        <label for="role-description" class="form-label">Description</label>
                        <textarea id="role-description" name="description" class="form-control" rows="6" placeholder="Enter description">{{ old('description', $role->description) }}</textarea>
                    </div>
                </div>
                <!-- End Grid -->

                <!-- Permissions Section -->
                <div class="p-4 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h5">Assign Permissions</h2>

                        <!-- Select All Checkbox -->
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input mt-1" id="select_all"
                                onclick="toggleAllPermissions(this)">
                            <label class="form-check-label" for="select_all">Select All Permissions</label>
                        </div>
                    </div>

                    <!-- Permissions Grid -->
                    <div class="row">
                        @foreach ($groupedPermissions as $group => $permissions)
                            @php
                                $hiddenPermissions = [
                                    'onboarding-index',
                                    'onboarding-create',
                                    'onboarding-update',
                                    'onboarding-approve',
                                    'onboarding-delete',
                                    'system-admin',
                                ];
                                $userRole = Auth::user()->role;
                                $isSuperAdmin = in_array('SystemAdmin', [$userRole->name]);
                                $hasVisiblePermissions = false;
                                foreach ($permissions as $permission) {
                                    if ($isSuperAdmin || !in_array($permission->name, $hiddenPermissions)) {
                                        $hasVisiblePermissions = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if ($hasVisiblePermissions)
                                <div class="col-lg-4 col-md-6 mb-5">
                                    <div class="card shadow-sm">
                                        <div
                                            class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">{{ ucfirst($group) }} Permissions</span>

                                            <!-- Group Checkbox -->
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input mt-1"
                                                    id="select_{{ $group }}"
                                                    onclick="toggleGroup('{{ $group }}', this)">
                                                <label class="form-check-label" for="select_{{ $group }}">Select
                                                    All</label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach ($permissions as $permission)
                                                @if ($isSuperAdmin || !in_array($permission->name, $hiddenPermissions))
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                            class="form-check-input permission-checkbox mt-1"
                                                            name="permissions[]" value="{{ $permission->name }}"
                                                            id="{{ $permission->name }}" data-group="{{ $group }}"
                                                            @if (in_array($permission->name, $rolePermissions)) checked @endif>
                                                        <label class="form-check-label" for="{{ $permission->name }}">
                                                            {{ str_replace('-', ' ', $permission->name) }}
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div> <!-- End of Permissions Grid -->
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-center mt-4">
                    @can('role-update')
                        <button type="submit" class="btn btn-primary">{{ _lang('Submit') }}</button>
                    @endcan
                </div>
            </div>
        </div>
        <!-- End Card -->
    </form>
    <!-- End Card Section -->
@endsection

@section('scripts')
    <script>
        // Toggle all permissions
        function toggleAllPermissions(checkbox) {
            // Select all permission checkboxes
            let allPermissions = document.querySelectorAll('.permission-checkbox');
            allPermissions.forEach((permissionCheckbox) => {
                permissionCheckbox.checked = checkbox.checked;
            });
        }

        // Toggle permissions in a specific group
        function toggleGroup(group, checkbox) {
            // Select permission checkboxes within the specified group
            let groupPermissions = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
            groupPermissions.forEach((permissionCheckbox) => {
                permissionCheckbox.checked = checkbox.checked;
            });
        }
    </script>
@endsection
