<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Academic\Repositories\UserRepository;
use Modules\Authentication\Models\User;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    /**
     * Get categories by filtering args.
     */
    public function get(array $args = []): Collection|Builder
    {
        $orderBy = empty($args['order_by']) ? 'id' : $args['order_by']; // column name
        $order = empty($args['order']) ? 'desc' : $args['order']; // asc, desc

        $query = User::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('created_by', getUserId())
            ->orderBy($orderBy, $order);

        if (isset($args['is_query']) && $args['is_query']) {
            return $query;
        }

        return $query->get();
    }

    public function getUsersAll(): Collection
    {
        return $this->userRepository->all();
    }

    public function getUsers(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage, $filter);
    }

    public function findUserById(int $id): ?User
    {
        return $this->userRepository->show((int) $id);
    }

    public function createOrUpdateUser(array $data, $id = null, $request = null): ?User
    {
        $data = $this->prepareForDB($data, $request);

        if ($id === null) {
            $data['institute_id'] = get_institute_id();
            $data['branch_id'] = get_branch_id();
            $data['created_by'] = getUserId();

            return $this->userRepository->create($data);
        } else {
            $result = $this->userRepository->update($data, (int) $id);

            if ($result) {
                return $this->findUserById((int) $id);
            } else {
                return null;
            }
        }
    }

    public function deleteUserById(int $id)
    {
        return $this->userRepository->delete($id);
    }

    public function getUsersByExistType($userType): Collection
    {
        return User::where('user_type', '=', $userType)
            ->select('id', 'name', 'phone', 'user_type')
            ->get();
    }

    public function getUsersByExcludedParentAndStudent(): Collection
    {
        return User::where('user_type', '!=', 'Parent')
            ->where('user_type', '!=', 'Student')
            ->orderBy('id', 'ASC')->get();
    }

    public function prepareForDB(array $data, ?Request $request = null): array
    {
        $preparedData = [];

        if (isset($data['institute_id'])) {
            $preparedData['institute_id'] = $data['institute_id'];
        }

        if (isset($data['branch_id'])) {
            $preparedData['branch_id'] = $data['branch_id'];
        }

        if (isset($data['email']) && ! empty($data['email'])) {
            $preparedData['email'] = $data['email'];
        }

        if (isset($data['phone']) && ! empty($data['phone'])) {
            $preparedData['phone'] = $data['phone'];
        }

        if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = fileUploader('users/', 'png', $data['image']);
        }

        $role = null;
        if (! empty($data['role_id'])) {
            $role = DB::table('roles')->where('id', $data['role_id'])->first();
        }
        if (! empty($data['name'])) {
            $preparedData['name'] = $data['name'];
            $preparedData['role_id'] = $role->id ?? null;
            $preparedData['user_type'] = $role->name ?? ($data['user_type'] ?? 'Student');
            $preparedData['image'] = $data['image'] ?? null;
            $preparedData['facebook'] = $data['facebook'] ?? '#';
            $preparedData['twitter'] = $data['twitter'] ?? '#';
            $preparedData['linkedin'] = $data['linkedin'] ?? '#';
            $preparedData['google_plus'] = $data['google_plus'] ?? '#';
        } else {
            $preparedData['name'] = $data['first_name'] ?? null;
            $preparedData['user_type'] = $data['user_type'] ?? 'Student';
        }

        $preparedData['username'] = ! empty($data['username'])
            ? $data['username']
            : $this->generateUniqueUsername($preparedData['name'] ?? 'user');

        if (isset($data['status'])) {
            $preparedData['status'] = $data['status'];
        }

        if (isset($data['password']) && ! empty($data['password'])) {
            $preparedData['password'] = Hash::make($data['password']);
        }

        return $preparedData;
    }

    /**
     * Generate a username derived from the given name, retrying with a random
     * numeric suffix until it is unique across all users.
     */
    public function generateUniqueUsername(?string $seed, ?string $secondSeed = null): string
    {
        $base = trim(($seed ?? '').' '.($secondSeed ?? ''));
        $base = Str::slug($base, '.');

        if ($base === '') {
            $base = 'user';
        }

        $username = $base;
        $attempt = 0;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $attempt++;
            $username = $base.random_int(100, 9999);

            // Extremely unlikely, but avoid an infinite loop under pathological collisions.
            if ($attempt > 20) {
                $username = $base.'-'.uniqid();
                break;
            }
        }

        return $username;
    }

    public function getUsersByExcludedType($userType): Collection
    {
        return User::select('id', 'name', 'user_type')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->with('userPayroll')
            ->orderByDesc('id')
            ->get();
    }
}
