<?php

namespace Modules\Library\DataTables;

use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Academic\Models\LibraryMember;
use Modules\Library\Repositories\LibraryMemberRepository;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LibraryMemberDataTable extends DataTable
{
    public function __construct(private readonly LibraryMemberRepository $libraryMemberRepository) {}

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('action', function (LibraryMember $libraryMember) {
                $html = '<a class="btn btn-link text-info" href="'.url('librarymembers-librarycard/'.$libraryMember->id).'" title="Library Card"><i class="fa fa-id-card"></i></a>';
                $html .= '<a class="btn btn-link text-info" href="'.route('librarymembers.edit', $libraryMember->id).'" title="Edit"><i class="fa fa-pencil"></i></a>';
                $html .= '<button type="button" class="btn btn-link text-danger" data-bs-toggle="modal" data-bs-target="#deleteLibraryMember'.$libraryMember->id.'"><i class="fa fa-trash"></i></button>';
                $html .= '
                <div class="modal fade" id="deleteLibraryMember'.$libraryMember->id.'" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Are you sure?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>
                                Are you sure to delete member - <b>'.e($libraryMember->library_id).'</b>
                            </p>
                            <form action="'.route('librarymembers.destroy', $libraryMember->id).'" method="POST">
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
            ->editColumn('name', function (LibraryMember $libraryMember) {
                $html = $libraryMember->user?->name;

                return $html;
            })
            ->editColumn('image', function (LibraryMember $libraryMember) {
                $imageUrl = asset('storage/users/'.$libraryMember->user?->image); // Generate the URL for the image

                return '<img src="'.$imageUrl.'" width="50px" alt="">'; // Return the HTML for the image
            })

            ->rawColumns(['name', 'image',  'action']);
    }

    public function query(LibraryMember $model): QueryBuilder
    {
        return $this->libraryMemberRepository->get(['is_query' => true]);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('library-member-table')
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
            Column::make('library_id')->title('Library Member ID'),
            Column::make('name')->title('Name'),
            Column::make('image')->title('Image'),
            Column::make('member_type')->title('Member Type'),
            Column::make('action')
                ->title('Action')
                ->searchable(false)
                ->orderable(false)
                ->printable(false),
        ];
    }

    protected function filename(): string
    {
        return 'LibraryMember-'.date('YmdHis');
    }
}
