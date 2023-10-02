<div id="slider-add" class="mfp-hide large">
    <div class="modal-data">
        <div class="modal-header px-0">
            <h5 class="modal-title">{{ __("Add Slider") }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('admin.setup.sections.section.item.store',$slug) }}" enctype="multipart/form-data">
                @csrf
                <div class="row mb-10-none mt-3">
                    <div class="language-tab">
                        <nav>
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <button class="nav-link @if (get_default_language_code() == language_const()::NOT_REMOVABLE) active @endif" id="modal-english-tab" data-bs-toggle="tab" data-bs-target="#modal-english" type="button" role="tab" aria-controls="modal-english" aria-selected="false">English</button>
                                @foreach ($languages as $item)
                                    <button class="nav-link @if (get_default_language_code() == $item->code) active @endif" id="modal-{{$item->name}}-tab" data-bs-toggle="tab" data-bs-target="#modal-{{$item->name}}" type="button" role="tab" aria-controls="modal-{{ $item->name }}" aria-selected="true">{{ $item->name }}</button>
                                @endforeach

                            </div>
                        </nav>
                        <div class="tab-content" id="nav-tabContent">

                            <div class="tab-pane @if (get_default_language_code() == language_const()::NOT_REMOVABLE) fade show active @endif" id="modal-english" role="tabpanel" aria-labelledby="modal-english-tab">
                                @php
                                    $default_lang_code = language_const()::NOT_REMOVABLE;
                                @endphp
                                <div class="form-group">
                                    @include('admin.components.form.input',[
                                        'label'     => __("Title")."*",
                                        'name'      => $default_lang_code . "_title",
                                        'value'     => old($default_lang_code . "_title",$data->value->language->$default_lang_code->title ?? "")
                                    ])
                                </div>
                                <div class="form-group">
                                    @include('admin.components.form.input',[
                                        'label'     => "Heading*",
                                        'name'      => $default_lang_code . "_heading",
                                        'value'     => old($default_lang_code . "_heading",$data->value->language->$default_lang_code->heading ?? "")
                                    ])
                                </div>
                                <div class="form-group">
                                    @include('admin.components.form.input',[
                                        'label'     => "Button Name*",
                                        'name'      => $default_lang_code . "_button_name",
                                        'value'     => old($default_lang_code . "_button_name",$data->value->language->$default_lang_code->button_name ?? "")
                                    ])
                                </div>
                            </div>

                            @foreach ($languages as $item)
                                @php
                                    $lang_code = $item->code;
                                @endphp
                                <div class="tab-pane @if (get_default_language_code() == $item->code) fade show active @endif" id="modal-{{ $item->name }}" role="tabpanel" aria-labelledby="modal-{{$item->name}}-tab">
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                        'label'     => __("Title")."*",
                                        'name'      => $lang_code . "_title",
                                        'value'     => old($lang_code . "_title",$data->value->language->$lang_code->title ?? "")
                                    ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => "Heading*",
                                            'name'      => $lang_code . "_heading",
                                            'value'     => old($lang_code . "_heading",$data->value->language->$lang_code->heading ?? "")
                                        ])
                                    </div>
                                    <div class="form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => "Button Name*",
                                            'name'      => $lang_code . "_button_name",
                                            'value'     => old($lang_code . "_button_name",$data->value->language->$lang_code->button_name ?? "")
                                        ])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.input',[
                            'label'     => "Button Link",
                            'name'      => "button_link",
                            'value'     => old("button_link",$data->value->button_link ?? ""),
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="custom-inner-card">
                            <div class="card-inner-header">
                                <h6 class="title">{{ __("Items") }}</h6>
                                <button type="button" class="btn--base input-field-generator"><i class="fas fa-plus"></i> {{ __("Add") }}</button>
                            </div>
                            <div class="card-inner-body">
                                <div class="results">
                                    @include('admin.components.slider.item')    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.input-file',[
                            'label'             => "Image:",
                            'name'              => "image",
                            'class'             => "file-holder",
                            'old_files_path'    => files_asset_path("site-section"),
                            'old_files'         => $data->value->items->image ?? "",
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                        <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base">{{ __("Add") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@push('script')
    <script>
        $(document).ready(function(){
            var sliderItemUrl = "{{ setRoute('admin.setup.sections.slider.add.items') }}";
            $('.input-field-generator').click(function(){
                $.get(sliderItemUrl,function(data){
                    $('.results').prepend(data);
                    $('.results').find('.row').first().find("select").select2();
                });
            });
        });
    </script>
@endpush
