<?php

declare(strict_types=1);

namespace Modules\Library\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Intervention\Image\Facades\Image;
use Modules\Library\Models\Book;
use Modules\Library\Repositories\BookRepository;

class BookService
{
    public function __construct(
        private readonly BookRepository $bookRepository
    ) {}

    public function getBooks(array $filter = [], $perPage = 50): LengthAwarePaginator
    {
        return $this->bookRepository->paginate($perPage);
    }

    public function findBookById(int $id): ?Book
    {
        return $this->bookRepository->show((int) $id);
    }

    public function createOrUpdateBook(array $data, $id = null): ?Book
    {
        $data = $this->prepareForDB($data);

        if ($id === null) {
            $data['institute_id'] = get_institute_id();
            $data['branch_id'] = get_branch_id();

            $book = $this->bookRepository->create($data);

            return $book;
        } else {
            $result = $this->bookRepository->update($data, intval($id));

            if ($result) {
                return $this->findBookById((int) $id);
            } else {
                return null;
            }
        }
    }

    public function deleteBookById(int $id): int
    {
        return $this->bookRepository->delete($id);
    }

    public function prepareForDB(array $data)
    {
        $preparedData = array_merge($data);

        if (isset($data['photo']) && $data['photo']->isValid()) {
            $photo = $data['photo'];
            $ImageName = time().'.'.$photo->getClientOriginalExtension();
            Image::make($photo)->resize(160, 160)->save(base_path('public/uploads/images/books/').$ImageName);
            $preparedData['photo'] = $ImageName;
        }

        if (isset($data['barcode']) && $data['barcode']->isValid()) {
            $barcode = $data['barcode'];
            $ImageName = time().'.'.$barcode->getClientOriginalExtension();
            Image::make($barcode)->resize(350, 200)->save(base_path('public/uploads/images/books/bar_codes/').$ImageName);
            $preparedData['barcode'] = $ImageName;
        }

        return $preparedData;
    }
}
