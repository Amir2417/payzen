@php
    $default_lang_code   = language_const()::NOT_REMOVABLE;
    $system_default_lang = get_default_language_code();
    $languages_for_js_use = $languages->toJson();
@endphp

@extends('admin.layouts.master')

@push('css')
    <link rel="stylesheet" href="{{ asset('public/backend/css/fontawesome-iconpicker.min.css') }}">
    <style>
        .fileholder {
            min-height: 374px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 330px !important;
        }
    </style>
@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Promotional Banner Section")])
@endsection

@section('content')
    <div class="table-area mt-15">
        <div class="table-wrapper">
            <div class="table-header justify-content-end">
                <div class="table-btn-area">
                    <a href="#promotional-banner-add" class="btn--base modal-btn"><i class="fas fa-plus me-1"></i> {{ __("Add Promotional Banner") }}</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ __("Button Name") }}</th>
                            <th>{{ __("Status") }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data->value->items ?? [] as $key => $item)
                            <tr data-item="{{ json_encode($item) }}">
                                <td>
                                    <ul class="user-list">
                                        <li><img src=" {{ get_image($item->image ?? "","site-section") ?? ""}} " alt="product"></li>
                                    </ul>
                                </td>
                                <td> {{ $item->language->$system_default_lang->button_name ?? "" }} </td>
                                <td>
                                    @include('admin.components.form.switcher',[
                                        'name'        => 'promotional_banner_status',
                                        'value'       => $item->status,
                                        'options'     => ['Enable' => 1, 'Disable' => 0],
                                        'onload'      => true,
                                        'data_target' => $item->id,
                                        'permission'  => "admin.setup.sections.promotional.banner.status.update",
                                    ])
                                </td>
                                <td>
                                    <button class="btn btn--base edit-modal-button"><i class="las la-pencil-alt"></i></button>
                                    <button class="btn btn--base btn--danger delete-modal-button" ><i class="las la-trash-alt"></i></button>
                                </td>
                            </tr>
                        @empty
                            @include('admin.components.alerts.empty',['colspan' => 4])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('admin.components.modals.site-section.promotional-banner-section.add')

    @include('admin.components.modals.site-section.promotional-banner-section.edit')
@endsection
@push('script')

    <script>
        openModalWhenError("promotional-banner-add","#promotional-banner-add");
        openModalWhenError("promotional-banner-edit","#promotional-banner-edit");

        var default_language = "{{ $default_lang_code }}";
        var system_default_language = "{{ $system_default_lang }}";
        var languages = "{{ $languages_for_js_use }}";
        languages = JSON.parse(languages.replace(/&quot;/g,'"'));

        $(".edit-modal-button").click(function(){
            var oldData   = JSON.parse($(this).parents("tr").attr("data-item"));
            var editModal = $("#promotional-banner-edit");

            // console.log(oldData);

            editModal.find("form").first().find("input[name=target]").val(oldData.id);
            editModal.find("input[name="+default_language+"_button_name_edit]").val(oldData.language[default_language].button_name);
            editModal.find("input[name=button_link]").val(oldData.button_link);
            
            $.each(languages,function(index,item){
                editModal.find("input[name="+item.code+"_button_name_edit]").val((oldData.language[item.code] == undefined ) ? '' : oldData.language[item.code].button_name);
            });
           
            editModal.find("input[name=image]").attr("data-preview-name",oldData.image);
            fileHolderPreviewReInit("#promotional-banner-edit input[name=image]");
           
            openModalBySelector("#promotional-banner-edit");

        });


        $(".delete-modal-button").click(function(){
            var oldData        = JSON.parse($(this).parents("tr").attr("data-item"));
            var actionRoute    = "{{ setRoute('admin.setup.sections.section.item.delete',$slug) }}";
            var target         = oldData.id;
            var message        = `Are you sure to <strong>delete</strong> this promotional banner?`;

            openDeleteModal(actionRoute,target,message);
        });
        
        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.setup.sections.promotional.banner.status.update',$slug) }}");
        })
        
    </script>
   
@endpush