@extends('layouts.backend')

@section('page_title_breadcrumb')
    {{ _lang('Chart Of Accounts') }}
@endsection
<style>
    ul,
    li {
        list-style: none;
    }

    .category {
        margin-bottom: 20px;
    }

    .group {
        margin-left: 20px;
        margin-bottom: 10px;
    }

    .ledger {
        margin-left: 40px;
        margin-bottom: 5px;
    }
</style>
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">
                        {{ _lang('Chart Of Accounts') }}
                    </div>
                </div>

                <div class="">
                    @foreach ($accountingCategories as $category)
                        <div class="category">
                            <h3>{{ $category->id }}. {{ $category->name }} => {{ $category->type }}</h3>
                            @if (isset($category->accountingGroups))
                                <ul>
                                    @foreach ($category->accountingGroups as $group)
                                        <li class="group">
                                            <h4> {{ $group->id }}. {{ $group->name }} - ({{ $category->nature }})</h4>

                                            @if (isset($group->accountingLedgers))
                                                <ul>
                                                    @foreach ($group->accountingLedgers as $ledger)
                                                        <li class="ledger">
                                                            <p>{{ $ledger->id }}. {{ $ledger->ledger_name }}</p>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
