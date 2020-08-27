@extends('layouts.app')

@section('content')
    @php
       // echo '<pre>';
        dd(get_queried_object())
    @endphp
  @while(have_posts()) @php the_post() @endphp
    @include('pages.content-page')
  @endwhile
@endsection
