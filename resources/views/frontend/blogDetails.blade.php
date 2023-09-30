@extends('frontend.layouts.master')

@php
$lang = selectedLang();
@endphp

@section('content')
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Blog
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
<section class="blog-section pt-200 pb-120">
    <div class="container">
        <div class="row mb-30">
            <div class="col-xl-8 col-lg-7 col-md-12 mb-30">
                <div class="row justify-content-center mb-30-none">
                    <div class="col-xl-12 mb-30">
                        <div class="blog-item-details">
                            <div class="blog-thumb">
                                <img src="{{ get_image(@$blog->image,'blog') }}" alt="blog">
                            </div>
                            <div class="blog-content">
                                <h3 class="title">{{ @$blog->name->language->$lang->name }}</h3>
                                @php
                                    echo @$blog->details->language->$lang->details;
                                @endphp
                                <div class="blog-social-area d-flex flex-wrap justify-content-between align-items-center">
                                    <h3 class="title">Share This Post</h3>
                                    <ul class="blog-social">
                                        <li><a href="#0"><i class="lab la-facebook-f"></i></a></li>
                                        <li><a href="#0" class="active"><i class="lab la-twitter"></i></a></li>
                                        <li><a href="#0"><i class="lab la-pinterest-p"></i></a></li>
                                        <li><a href="#0"><i class="lab la-linkedin-in"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-lg-5 col-md-12 mb-30">
                <div class="blog-sidebar">
                    <div class="widget-box mb-30">
                        <h4 class="widget-title">{{ __("Recent Posts") }}</h4>
                        <div class="popular-widget-box">
                            @foreach ($recentPost as $post)
                            <div class="single-popular-item d-flex flex-wrap align-items-center">
                                <div class="popular-item-thumb">
                                    <a href="{{route('blog.details',[$post->id, @$post->slug])}}"><img src="{{ get_image(@$post->image,'blog') }}" alt="blog"></a>
                                </div>
                                <div class="popular-item-content">
                                    <span class="date">{{ $post->created_at->diffForHumans() }}</span>
                                    <h5 class="title"><a href="{{route('blog.details',[$post->id, @$post->slug])}}">{{ @$post->name->language->$lang->name }}</a></h5>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="widget-box">
                        <h4 class="widget-title">{{ __("Category") }}</h4>
                        <div class="tag-widget-box">
                            <ul class="tag-list">
                                @foreach ($categories ?? [] as $cat)
                                @php
                                    $blogCount = App\Models\Blog::active()->where('category_id',$cat->id)->count();
                                @endphp
                                    @if( $blogCount > 0)
                                    <li><a href="{{ setRoute('blog.by.category',[$cat->id, slug(@$cat->name)]) }}"> {{ __(@$cat->name) }}<span>{{ @$blogCount }}</span></a></li>
                                    @else
                                    <li><a href="javascript:void(0)"> {{ __(@$cat->name) }}<span>{{ @$blogCount }}</span></a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Blog
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection

@push("script")

@endpush
