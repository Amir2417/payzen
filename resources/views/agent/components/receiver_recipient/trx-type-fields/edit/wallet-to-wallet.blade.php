
@php
    $details = json_decode(@$data->details);
@endphp
<div class="trx-input">
    <div class="row">
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            <label>{{ __("Country") }}<span>*</span></label>
            <select name="country" class="form--control country-select select2-basic " data-minimum-results-for-search="Infinity">
                <option selected disabled>Select Country</option>
                @foreach ($countries as $item)
                 @if(get_default_currency_code() == $item->code)
                    <option value="{{ $item->id }}" {{ $item->id == @$data->country?'selected':'' }} data-country-code="{{ $item->code }}" data-mobile-code="{{ $item->mobile_code }}"  data-id="{{ $item->id }}">{{ $item->country }} ({{ $item->code }})</option>
                 @endif
                @endforeach
            </select>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            {{-- <label>{{_("Phone Number")}} <span class="text--base">*</span></label> --}}
            {{-- <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text copytext">+{{ getDialCode() }}</span>
                </div>
                <input type="number" name="mobile" required class="form--control mobile" placeholder="Enter Mobile Number" value="{{ @$data->mobile }}">
            </div> --}}
            <label>{{ __("Phone Number") }}<span>*</span></label>
            <div class="input-group">
              <div class="input-group-text phone-code">+{{ @$data->mobile_code }}</div>
              <input class="phone-code" type="hidden" name="mobile_code" value="{{ @$data->mobile_code }}"/>
              <input type="text" class="form--control mobile" placeholder="Enter Mobile" name="mobile" value="{{ @$data->mobile }}">
            </div>
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
                'name'          => "firstname",
                'label'         => "First Name",
                'label_after'   => "<span>*</span>",
                'placeholder'   => "First Name...",
                'attribute'     => "readonly",
                'value'     => @$data->firstname,
            ])
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
                'label'         => "Last Name",
                'label_after'   => "<span>*</span>",
                'name'          => "lastname",
                'placeholder'   => "Last Name...",
                'attribute'     => "readonly",
                'value'     => @$data->lastname,
            ])
        </div>
        {{-- <div class="col-xl-4 col-lg-4 col-md-6 form-group country-select-wrp">
            @include('admin.components.form.input',[
                'label'         => "Country",
                'label_after'   => "<span>*</span>",
                'name'          => "country",
                'placeholder'   => "Country",
                'attribute'     => "readonly",
                'value'     => @$details->address->country,

            ])
        </div> --}}
        <div class="col-xl-4 col-lg-4 col-md-6 form-group state-select-wrp">
            @include('admin.components.form.input',[
                'label'         => "Address",
                'label_after'   => "<span>*</span>",
                'name'          => "address",
                'placeholder'   => "Address",
                'attribute'     => "readonly id=place-input autocomplete=none",
                'value'     => @$data->address,
            ])
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group state-select-wrp">
            @include('admin.components.form.input',[
                'label'         => "State",
                'name'          => "state",
                'placeholder'   => "State",
                'attribute'     => "readonly",
                'value'     => @$data->state,
            ])
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group city-select-wrp">
            @include('admin.components.form.input',[
                'label'         => "City",
                'label_after'   => "<span>*</span>",
                'name'          => "city",
                'placeholder'   => "City",
                'attribute'     => "readonly",
                'value'     => @$data->city,
            ])
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 form-group">
            @include('admin.components.form.input',[
                'label'         => "Zip Code",
                'label_after'   => "<span>*</span>",
                'name'          => "zip",
                'type'          => "text",
                'placeholder'   => "Zip Code",
                'attribute'     => "readonly",
                'value'     => @$data->zip_code,
            ])
        </div>

    </div>
</div>
