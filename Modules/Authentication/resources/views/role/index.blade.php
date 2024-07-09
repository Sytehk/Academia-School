@extends('layouts.backend')
@section('page_title_breadcrumb')
    {{ _lang('Role with Permissions') }}
@endsection

@section('create-button')
    <a href="{{ route('roles.create') }}" class="btn btn-sm fw-bold btn-primary">
        Create Role
    </a>
@endsection

@section('content')
    <div class="table-responsive">
        <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
            <thead>
                <tr>
                    <th width="10%">{{ _lang('Role Name') }}</th>
                    <th width="70%">{{ _lang('Permissions') }}</th>
                    <th width="10%">{{ _lang('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    @if ($role->name != 'Hostel' && $role->name != 'Transport')
                        <tr>
                            <td width="10%">
                                <span class="fw-semibold text-dark">
                                    {{ $role->name }} ({{ $role->guard_name }})
                                </span>
                            </td>

                            <td width="70%" class="align-middle">
                                @php
                                    $rolePermissions = \Illuminate\Support\Facades\DB::table('role_has_permissions')
                                        ->where('role_has_permissions.role_id', $role->id)
                                        ->pluck('role_has_permissions.permission_id')
                                        ->all();
                                    $permissions = \Spatie\Permission\Models\Permission::whereIn(
                                        'id',
                                        $rolePermissions,
                                    )->get();
                                @endphp

                                <div class="d-flex flex-wrap gap-2">
                                    @foreach ($permissions as $permission)
                                        <span class="badge bg-info text-white">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td width="10%">
                                <div class="d-flex justify-content-end gap-2">
                                    @can('role_update')
                                        <a class="text-decoration-none text-primary"
                                            href="{{ route('roles.edit', $role->id) }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan
                                    @can('role_delete')
                                        <form action="{{ route('roles.destroy', $role->id) }}" method="post" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">{{ _lang('No_Data_Found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
