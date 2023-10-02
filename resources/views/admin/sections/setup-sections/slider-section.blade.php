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
    ], 'active' => __("Slider Section")])
@endsection

@section('content')
    <div class="table-area mt-15">
        <div class="table-wrapper">
            <div class="table-header justify-content-end">
                <div class="table-btn-area">
                    <a href="#slider-add" class="btn--base modal-btn"><i class="fas fa-plus me-1"></i> {{ __("Add Slider") }}</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>{{ __("Heading") }}</th>
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
                                <td> {{ $item->language->$system_default_lang->heading ?? "" }} </td>
                                <td>
                                    @include('admin.components.form.switcher',[
                                        'name'        => 'slider_status',
                                        'value'       => $item->status,
                                        'options'     => ['Enable' => 1, 'Disable' => 0],
                                        'onload'      => true,
                                        'data_target' => $item->id,
                                        'permission'  => "admin.setup.sections.slider.status.update",
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
    @include('admin.components.modals.site-section.slider-section.add')

    @include('admin.components.modals.site-section.slider-section.edit')
@endsection
@push('script')
<script src="{{ asset('public/backend/js/fontawesome-iconpicker.js') }}"></script>
    <script>
        // icon picker
        $('.icp-auto').iconpicker();
        $(".input-field-generator").click(function(){
            // alert();
            setTimeout(() => {
                $('.icp-auto').iconpicker();
            }, 1500);
        });
    </script>
    <script>
        openModalWhenError("slider-add","#slider-add");
        openModalWhenError("slider-edit","#slider-edit");

        var default_language = "{{ $default_lang_code }}";
        var system_default_language = "{{ $system_default_lang }}";
        var languages = "{{ $languages_for_js_use }}";
        languages = JSON.parse(languages.replace(/&quot;/g,'"'));

        $(".edit-modal-button").click(function(){
            var oldData   = JSON.parse($(this).parents("tr").attr("data-item"));
            var editModal = $("#slider-edit");

            editModal.find("form").first().find("input[name=target]").val(oldData.id);
            editModal.find("input[name="+default_language+"_title_edit]").val(oldData.language[default_language].title);
            editModal.find("input[name="+default_language+"_heading_edit]").val(oldData.language[default_language].heading);
            editModal.find("input[name="+default_language+"_item_title_edit]").val(oldData.language[default_language].item_title);
            editModal.find("input[name="+default_language+"_button_name_edit]").val(oldData.language[default_language].button_name);
            editModal.find("input[name=button_link]").val(oldData.button_link);

            
            $.each(languages,function(index,item){
                editModal.find("input[name="+item.code+"_title_edit]").val((oldData.language[item.code] == undefined ) ? '' : oldData.language[item.code].title);
                editModal.find("input[name="+item.code+"_heading_edit]").val((oldData.language[item.code] == undefined ) ? '' : oldData.language[item.code].heading);
                editModal.find("input[name="+item.code+"_item_title_edit]").val((oldData.language[item.code] == undefined ) ? '' : oldData.language[item.code].item_title);
                editModal.find("input[name="+item.code+"_button_name_edit]").val((oldData.language[item.code] == undefined ) ? '' : oldData.language[item.code].button_name);
            });
            var itemData = `<div class="row align-items-end">
                                <div class="col-xl-4 col-lg-4 form-group">
                                    <label for="icon">Icon*</label>
                                    <input type="text" placeholder="Type Here..." name="icon[]" class="form--control form--control icp icp-auto iconpicker-element iconpicker-input" value="">
                                </div>
                                <div class="col-xl-4 col-lg-4 form-group">
                                    <label for="item title">Item Title*</label>
                                    <input type="text" placeholder="Type Here..." name="item_title[]" value="">
                                </div>
                                <div class="col-xl-3 col-lg-3 form-group">
                                    <label for="Counter Value">Counter Value*</label>
                                    <input type="number" placeholder="Type Here..." name="counter_value[]" value="">
                                </div>
                                <div class="col-xl-1 col-lg-1 form-group">
                                    <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100"><i class="las la-times"></i></button>
                                </div>
                            </div>`;
            if (oldData.item.length > 0) {
               itemData = ""; 
            }
            $.each(oldData.item,function(index,item){
                itemData    += `<div class="row align-items-end">
                                    <div class="col-xl-4 col-lg-4 form-group">
                                        <label for="icon">Icon*</label>
                                        <input type="text" placeholder="Type Here..." name="icon[]" class="form--control form--control icp icp-auto iconpicker-element iconpicker-input" value="${item.icon}">
                                    </div>
                                    <div class="col-xl-4 col-lg-4 form-group">
                                        <label for="item title">Item Title*</label>
                                        <input type="text" placeholder="Type Here..." name="item_title[]" value="${item.item_title}">
                                    </div>
                                    <div class="col-xl-3 col-lg-3 form-group">
                                        <label for="Counter Value">Counter Value*</label>
                                        <input type="number" placeholder="Type Here..." name="counter_value[]" value="${item.counter_value}">
                                    </div>
                                    <div class="col-xl-1 col-lg-1 form-group">
                                        <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100"><i class="las la-times"></i></button>
                                    </div>
                                </div>`;
            });
            editModal.find(".results").html(itemData);
            editModal.find("input[name=image]").attr("data-preview-name",oldData.image);
            fileHolderPreviewReInit("#slider-edit input[name=image]");
           
            openModalBySelector("#slider-edit");

        });


        $(".delete-modal-button").click(function(){
            var oldData        = JSON.parse($(this).parents("tr").attr("data-item"));
            var actionRoute    = "{{ setRoute('admin.setup.sections.section.item.delete',$slug) }}";
            var target         = oldData.id;
            var message        = `Are you sure to <strong>delete</strong> this slider?`;

            openDeleteModal(actionRoute,target,message);
        });
        
        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.setup.sections.slider.status.update',$slug) }}");
        })
        
    </script>
   
@endpush