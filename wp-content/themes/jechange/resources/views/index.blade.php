@php
    // ACT LIKE ROUTING SYSTEM

    $queriedObject = get_queried_object();
    // Check if its Post or Taxonomy
    $is_post = !isset($queriedObject->term_id) ? true : false;

@endphp

@extends('layouts.app')

@if($is_post)
    @php
    $id = $queriedObject->ID;
    $post_type = get_post_type($post);  // post, page, providers, guide, ....
    @endphp

    @if($post_type == 'page')

    @endif

    @if($post_type == 'providers')
        @section('content')
            @include('pages.content-single-providers', ['data' => \App\Controllers\SingleProviders::data()])
        @endsection
    @endif

    @if($post_type == 'post')
        @section('content')
            @include('pages.content-single-post', ['data' => \App\Controllers\SinglePost::data()])
        @endsection
    @endif

    @if($post_type == 'guides')
        @section('content')
            @include('pages.content-single-guides', ['data' => \App\Controllers\SingleGuides::data()])
        @endsection
    @endif

    @if($post_type == 'provider_article')
        @section('content')
            @include('pages.content-single-provider-article', ['data' => \App\Controllers\SingleProviderArticle::data()])
        @endsection
    @endif

    @if($post_type == 'press_release')
        @section('content')
            @include('pages.content-single-press-release', ['data' => \App\Controllers\SinglePressRelease::data()])
        @endsection
    @endif

    @if($post_type == 'press_review')
        @section('content')
            @include('pages.content-single-press-review', ['data' => \App\Controllers\SinglePressReview::data()])
        @endsection
    @endif

@endif
