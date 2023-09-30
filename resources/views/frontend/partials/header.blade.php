@php
    $type = App\Constants\GlobalConst::SETUP_PAGE;
    $menues = DB::table('setup_pages')
            ->where('status', 1)
            ->where('type', Str::slug($type))
            ->get();
    $contact_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::CONTACT_SECTION);
    $contact = App\Models\Admin\SiteSections::getData( $contact_slug)->first();
@endphp
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<header class="header-section {{ $class ?? "" }}">
    <div class="header">
        <div class="header-logo">
            <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ get_logo($basic_settings) }}" alt="site-logo"></a>
        </div>
        <div class="header-box-btn">
            <a href="" class="header-btn"><span><i class="las la-calendar-week"></i></span>Registration</a>
        </div>
        <div class="container">
            <div class="header-bottom-area">
                <div class="header-menu-content">
                    <nav class="navbar navbar-expand-lg p-0">
                        <a class="site-logo site-title" href="{{ setRoute('index') }}"><img src="{{ get_logo($basic_settings) }}" alt="site-logo"></a>
                        <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="fas fa-bars"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarSupportedContent">
                            <ul class="navbar-nav main-menu me-auto">
                                @php
                                    $current_url = URL::current();
                                @endphp
                                @foreach ($menues as $item)
                                    @php
                                        $title = json_decode($item->title);
                                    @endphp
                                <li><a href="{{ url($item->url) }}" class="@if ($current_url == url($item->url)) active @endif">{{ __($title->title) }}<i class="las la-arrow-down"></i></a></li>
                                @endforeach
                            </ul>
                            <div class="header-action">
                                <div class="header-call-action">
                                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="14" height="14" x="0" y="0" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path fill-rule="evenodd" d="M219.294 138.047 183.864 5.932A8.017 8.017 0 0 0 176.132 0H36.71A36.145 36.145 0 0 0 .624 34.228 453.721 453.721 0 0 0 453.756 512c8.014 0 16.028-.211 24-.633a36.153 36.153 0 0 0 34.258-36.1V335.843a7.989 7.989 0 0 0-5.952-7.719l-132.111-35.4a35.3 35.3 0 0 0-34.914 9.349l-45.318 45.341a19.855 19.855 0 0 1-25.072 2.775c-19.73-12.527-42.412-27.439-60.877-45.937-18.511-18.5-33.461-41.151-45.974-60.928A19.777 19.777 0 0 1 164.6 218.3l45.318-45.336a35.382 35.382 0 0 0 9.373-34.919zm72.874-90.987a47.052 47.052 0 1 1 47.052 47.061 47.128 47.128 0 0 1-47.052-47.061zm94.1 125.7a47.052 47.052 0 1 1-47.048-47.06 47.1 47.1 0 0 1 47.052 47.061zm31.586-125.7a47.076 47.076 0 1 1 47.056 47.061 47.128 47.128 0 0 1-47.052-47.061zm47.056 78.64a47.059 47.059 0 1 1-47.052 47.061A47.138 47.138 0 0 1 464.91 125.7z" data-original="#000000" class=""></path></g></svg>
                                    <a href="tel:{{ __(@$contact->value->language->$lang->mobile) }}">+{{ __(@$contact->value->language->$lang->mobile) }}</a>
                                </div>
                                <div class="header-account-action login">
                                    <a href="{{ setRoute('user.login') }}">
                                        <svg clip-rule="evenodd" fill-rule="evenodd" height="14" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 24 24" width="14" xmlns="http://www.w3.org/2000/svg" id="fi_7542181"><g id="Icon"><circle cx="11.5" cy="6.744" r="5.5"></circle><path d="m11.25 21.756v-2.055c0-.465.184-.91.513-1.238l1.99-1.99c-.049-1.084.255-2.182.908-3.106-.993-.169-2.056-.261-3.161-.261-3.322 0-6.263.831-8.089 2.076-1.393.95-2.161 2.157-2.161 3.424v1.45c0 .451.179.884.498 1.202.319.319.751.498 1.202.498z"></path><path d="m18.152 20.208c1.212.182 2.493-.194 3.426-1.127 1.562-1.562 1.562-4.098 0-5.659-1.561-1.562-4.097-1.562-5.659 0-.933.933-1.309 2.214-1.127 3.427 0-.001-2.322 2.321-2.322 2.321-.141.141-.22.332-.22.531v2.299c0 .414.336.75.75.75h2.299c.199 0 .39-.079.531-.22zm-.17-3.19c-.423-.423-.423-1.11 0-1.533s1.11-.423 1.533 0 .423 1.11 0 1.533-1.11.423-1.533 0z"></path></g></svg>
                                    </a>
                                </div>
                                <div class="header-account-action">
                                    <a href="{{ setRoute('user.register') }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:svgjs="http://svgjs.com/svgjs" width="14" height="14" x="0" y="0" viewBox="0 0 48 48" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M23.29 35a11 11 0 0 0 2.51 7h-20a1 1 0 0 1-1-.89 12.52 12.52 0 0 1-.08-1.53 18.6 18.6 0 0 1 11.36-17.13 11.47 11.47 0 0 0 14.42 0 18.41 18.41 0 0 1 3 1.58A11 11 0 0 0 23.29 35z" data-original="#000000" class=""></path><circle cx="23.29" cy="13.5" r="9.5" data-original="#000000" class=""></circle><path d="M34.29 26a9 9 0 1 0 9 9 9 9 0 0 0-9-9zm4 10h-3v3a1 1 0 0 1-2 0v-3h-3a1 1 0 1 1 0-2h3v-3a1 1 0 1 1 2 0v3h3a1 1 0 0 1 0 2z" data-original="#000000" class=""></path></g></svg>
                                    </a>
                                </div>
                                <div class="header-theme-action">
                                    <button class="mode-button"><i class="las la-sun"></i></button>
                                </div>
                                <div class="header-language-action">
                                    @php
                                        $session_lan = session('local')??get_default_language_code();
                                    @endphp
                                    <select class=" nice-select form--control">
                                        @foreach($__languages as $item)
                                        <option value="{{$item->code}}" @if( $session_lan == $item->code) selected  @endif>{{ __($item->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</header>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Header
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
