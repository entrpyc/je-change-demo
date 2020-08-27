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

@endif
