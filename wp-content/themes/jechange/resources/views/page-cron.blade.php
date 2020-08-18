{{--
  Template Name: Cron page
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php the_post() @endphp
    @include('pages.content-cron-page')
  @endwhile
@endsection
