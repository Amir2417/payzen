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
                <div class="row mb-50-none">
                    @foreach ($blogs?? [] as $blog)
                        <div class="col-xl-6 col-lg-6 col-md-6 mb-50">
                            <div class="blog-item">
                                <div class="thumb">
                                    <img src="{{ get_image(@$blog->image,'blog') }}" alt="">
                                </div>
                                <div class="content">
                                    <a href="{{route('blog.details',[$blog->id,$blog->slug])}}" class="title">
                                        <h3>{{ @$blog->name->language->$lang->name }}</h3>
                                    </a>
                                    <p>{{textLength(strip_tags(@$blog->details->language->$lang->details,120))}}</p>
                                    <a href="{{route('blog.details',[$blog->id,$blog->slug])}}" class="blog-item-btn">{{ __("Read More") }} <i class="las la-angle-right"></i></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                                        <a href="{{ route('blog.details',[$post->id, @$post->slug]) }}"><img src="{{ get_image(@$post->image,'blog') }}" alt="blog"></a>
                                    </div>
                                    <div class="popular-item-content">
                                        <span class="date">{{ $post->created_at->diffForHumans() }}</span>
                                        <h5 class="title"><a href="{{ route('blog.details',[$post->id, @$post->slug]) }}">{{ @$post->name->language->$lang->name }}</a></h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="widget-box">
                        <h4 class="widget-title">{{ __("Categories") }}</h4>
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
        <nav>
            <ul class="pagination">
                {{ get_paginate($blogs) }}
            </ul>
        </nav>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Blog
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
@endsection
@push("script")
@endpush
