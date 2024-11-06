@extends('admin.layouts.master')

@section('title', __('Label.Notification'))

@section('content')
    <div class="body-content">
        <!-- mobile title -->
        <h1 class="page-title-sm"> @yield('title') </h1>

        <div class="border-bottom row">
            <div class="col-sm-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Label.Dashboard')}}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{__('Label.Notification List')}}</li>
                </ol>
            </div>
            <div class="col-sm-12 mb-3 d-flex justify-content-between">
                <a href="{{ route('notificationAdd') }}" class="btn btn-default mw-120">{{__('Label.Add')}}</a>
                <a href="{{ route('notificationSetting') }}" class="btn btn-default mw-120">{{__('Label.Notification Setting')}}</a>
            </div>
        </div>

        <!-- Search -->
        <div class="page-search mb-3 mt-3">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1"><img src="{{ asset('assets/imgs/search.png') }}"></span>
                </div>
                <input type="text" id="input_search" class="form-control" placeholder="Search Notification" aria-label="Search" aria-describedby="basic-addon1">
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-striped notification-table text-center table-bordered">
                <thead>
                    <tr style="background: #F9FAFF;">
                        <th> {{__('Label.#')}} </th>
                        <th> {{__('Label.Image')}} </th>
                        <th> {{__('Label.Title')}} </th>
                        <th> {{__('Label.Message')}} </th>
                        <th> {{__('Label.Action')}} </th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@section('pagescript')
    <script>
        $(document).ready(function() {
            $(function() {
                var table = $('.notification-table').DataTable({
                    dom: "<'top'f>rt<'row'<'col-2'i><'col-1'l><'col-9'p>>",
                    searching: false,
                    responsive: true,
                    autoWidth: false,
                    processing: true,
                    serverSide: true,
                    lengthMenu: [[10, 100, 500, -1], [10, 100, 500, "All"]],
                    language: {
                        paginate: {
                            previous: "<img src='{{url('assets/imgs/left-arrow.png')}}' >",
                            next: "<img src='{{url('assets/imgs/left-arrow.png')}}' style='transform: rotate(180deg)'>"
                        }
                    },
                    ajax: {
                        url: "{{ route('notificationData') }}",
                        data: function(d) {
                            d.input_search = $('#input_search').val();
                        },
                    },
                    columns: [
                        {data: 'DT_RowIndex', name: 'DT_RowIndex'},
                        {data: 'image', name: 'image', orderable: false, searchable: false,
                            render: function(data, type, full, meta) {
                                return "<a href='" + data + "' target='_blank' title='Watch'><img src='" + data + "' class='img-thumbnail' style='height:55px; width:55px'></a>";
                            },
                        },
                        {data: 'title',name: 'title'},
                        {data: 'message',name: 'message'},
                        {data: 'action', name: 'action', orderable: false, searchable: false},
                    ],
                });

                $('#input_search').keyup(function() {
                    table.draw();
                });
            });
        });
    </script>
@endsection