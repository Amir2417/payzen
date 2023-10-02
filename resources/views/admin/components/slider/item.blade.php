@forelse ($items ?? [] as $item)
<div class="row align-items-end">

    <div class="col-xl-4 col-lg-4 form-group">
        @include('admin.components.form.input',[
            'label'         => __("Icon")."*",
            'name'          => "icon[]",
            'class'         => "form--control icp icp-auto iconpicker-element iconpicker-input",
            'value'         => $item->icon
        ])
    </div>
    <div class="col-xl-4 col-lg-4 form-group">
        @include('admin.components.form.input',[
            'label'         => __("Item Title")."*",
            'name'          => "item_title[]", 
        ])
    </div>
    <div class="col-xl-3 col-lg-3 form-group">
        @include('admin.components.form.input',[
            'label'         => __("Counter Value")."*",
            'type'          => 'number',
            'name'          => "counter_value[]",
            
        ])
    </div>
    <div class="col-xl-1 col-lg-1 form-group">
        <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100"><i class="las la-times"></i></button>
    </div>
</div>  
@empty
<div class="row align-items-end">

    <div class="col-xl-4 col-lg-4 form-group">
        @include('admin.components.form.input',[
            'label'         => __("Icon")."*",
            'name'          => "icon[]",
            'class'         => "form--control icp icp-auto iconpicker-element iconpicker-input",
            'value'         => old("icon")
        ])
    </div>
    <div class="col-xl-4 col-lg-4 form-group">
        @include('admin.components.form.input',[
            'label'         => __("Item Title")."*",
            'name'          => "item_title[]", 
        ])
    </div>
    <div class="col-xl-3 col-lg-3 form-group">
        @include('admin.components.form.input',[
            'label'         => __("Counter Value")."*",
            'type'          => 'number',
            'name'          => "counter_value[]",
            
        ])
    </div>
    <div class="col-xl-1 col-lg-1 form-group">
        <button type="button" class="custom-btn btn--base btn--danger row-cross-btn w-100"><i class="las la-times"></i></button>
    </div>
</div>  
@endforelse
