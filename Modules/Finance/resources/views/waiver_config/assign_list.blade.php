 <div class="card card-default">
     <div class="card-header"><span class="card-title">{{ _lang('Waiver List') }}</span></div>

     <div class="">
         <div class="row">
             <div class="col-lg-12">
                 <div class="table-responsive mt-5">
                     @if (\Session::has('success'))
                         <div class="alert alert-success">
                             <p>{{ \Session::get('success') }}</p>
                         </div>
                         <br />
                     @endif
                     <table class="table align-middle table-row-dashed fs-6 gy-5 data-table">
                         <thead>
                             <tr>
                                 <th>{{ _lang('SL NO.') }}</th>
                                 <th>{{ _lang('Student Roll') }}</th>
                                 <th>{{ _lang('Student Name') }}</th>
                                 <th>{{ _lang('Fee Head') }}</th>
                                 <th>{{ _lang('Waiver') }}</th>
                                 <th>{{ _lang('Amount') }}</th>
                                 <th>{{ _lang('Action') }}</th>
                             </tr>
                         </thead>
                         <tbody>

                             @foreach ($waiverConfigLists as $waiverConfigList)
                                 <tr>
                                     <td>{{ $loop->index + 1 }}</td>
                                     <td>{{ $waiverConfigList->roll }}</td>
                                     <td><span
                                             style="text-transform: capitalize">{{ $waiverConfigList->first_name }}</span>
                                         <span
                                             style="text-transform: capitalize">{{ $waiverConfigList->last_name }}</span>
                                     </td>
                                     <td>{{ $waiverConfigList->name }}</td>
                                     <td>{{ $waiverConfigList->waiver }}</td>
                                     <td>{{ $waiverConfigList->amount }}</td>
                                     <td>
                                         <form action="{{ route('waiver-config.destroy', $waiverConfigList->id) }}"
                                             method="post">
                                             @method('DELETE')
                                             @csrf
                                             <button type="submit" class="btn btn-danger btn-sm show_confirm"><i
                                                     class="fa fa-trash" aria-hidden="true"></i></button>
                                         </form>

                                     </td>
                                 </tr>
                             @endforeach
                         </tbody>
                     </table>
                 </div>
             </div>
         </div>

     </div>
 </div>
