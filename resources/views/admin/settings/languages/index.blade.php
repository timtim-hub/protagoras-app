@extends('layouts.app')

@section('css')
    <!-- Data Table CSS -->
    <link href="{{URL::asset('plugins/datatable/datatables.min.css')}}" rel="stylesheet" />
@endsection

@section('page-header')
    <div class="page-header mt-5-7">
        <div class="page-leftheader">
            <h4 class="page-title mb-0">{{ __('Language Settings') }}</h4>
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="fa fa-cogs mr-2 fs-12"></i>{{ __('Admin') }}</a></li>
                <li class="breadcrumb-item" aria-current="page"><a href="{{ route('admin.settings.index') }}"> {{ __('Settings') }}</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a href="#"> {{ __('Language Settings') }}</a></li>
            </ol>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-xm-12">
            <div class="card border-0">
                <div class="card-header">
                    <h3 class="card-title">{{ __('Language Management') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('elseyyid.translations.lang.newLang') }}" class="btn btn-primary">{{ __('Add New Language') }}</a>
                    </div>
                </div>
                <div class="card-body pt-2">
                    <!-- SET DATATABLE -->
                    <table id='languagesTable' class='table' width='100%'>
                        <thead>
                            <tr>
                                <th width="10%">{{ __('Language Code') }}</th>
                                <th width="20%">{{ __('Language Name') }}</th>
                                <th width="20%">{{ __('Translation Progress') }}</th>
                                <th width="20%">{{ __('Last Updated') }}</th>
                                <th width="30%">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                    </table> <!-- END SET DATATABLE -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <!-- Data Tables JS -->
    <script src="{{URL::asset('plugins/datatable/datatables.min.js')}}"></script>
    <script type="text/javascript">
        $(function () {
            "use strict";
            
            // INITILIZE DATATABLE
            var table = $('#languagesTable').DataTable({
                "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
                responsive: true,
                colReorder: true,
                "order": [[ 0, "desc" ]],
                language: {
                    search: "<i class='fa fa-search search-icon'></i>",
                    lengthMenu: '_MENU_ ',
                    paginate : {
                        first    : '<i class="fa fa-angle-double-left"></i>',
                        last     : '<i class="fa fa-angle-double-right"></i>',
                        previous : '<i class="fa fa-angle-left"></i>',
                        next     : '<i class="fa fa-angle-right"></i>'
                    }
                },
                pagingType : 'full_numbers'
            });

        });
    </script>
@endsection 