@if (admin_permission_by_name("admin.bill.pay.category.store"))
    <div id="category-add" class="mfp-hide large">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Add New Category") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.bill.pay.category.store') }}">
                    @csrf
                    <div class="row mb-10-none">

                        <div class="col-xl-12 col-lg-12 form-group mt-2">
                            @include('admin.components.form.input',[
                                'label'         => "Category Name*",
                                'name'          => "name",
                                'value'         => old("name"),
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
            openModalWhenError("category-add","#category-add");
        </script>
    @endpush
@endif
