<?php

namespace Modules\Library\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Library\Models\Book;
use Modules\Library\Repositories\BookRepository;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BookDataTable extends DataTable
{
    public function __construct(private readonly BookRepository $bookRepository) {}

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function (Book $book) {
                $html = '<a class="btn btn-link text-info" href="'.route('books.edit', $book->id).'"><i class="fa fa-pencil"></i></a>';
                $html .= '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#deleteBook'.$book->id.'" text-danger"><i class="fa fa-trash"></i></button>';
                $html .= '
                <div class="modal fade" id="deleteBook'.$book->id.'" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Are you sure?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Are you sure to delete category - <b>'.$book->book_name.'</b>
                            </p>
                            <form action="'.route('books.destroy', $book->id).'" method="POST">
                                '.csrf_field().method_field('DELETE').'

                                <button type="submit" class="btn btn-danger">Confirm Delete</button>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
                ';

                return $html;
            })
            ->editColumn('book_name', function (Book $book) {
                $html = $book->book_name;
                $html .= '<br /> <a class="btn btn-link text-primary" target="_blank" href="'.route('books.show', $book->id).'">View</a>';

                return $html;
            })

            ->rawColumns(['book_name', 'action']);
    }

    public function query(Book $model): QueryBuilder
    {
        return $this->bookRepository->get(['is_query' => true]);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('book-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                // Button::make('add'),
                // Button::make('excel'),
                // Button::make('csv'),
                // Button::make('pdf'),
                // Button::make('print'),
                // Button::make('reset'),
                // Button::make('reload'),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('Sl no')->searchable(false)->orderable(false),
            Column::make('book_name')->title('Book Name'),
            Column::make('code')->title('Code'),
            Column::make('author')->title('Author'),
            Column::make('quantity')->title('Quantity'),
            Column::make('action')
                ->title('Action')
                ->searchable(false)
                ->orderable(false)
                ->printable(false),
        ];
    }

    protected function filename(): string
    {
        return 'Book-'.date('YmdHis');
    }
}
