@extends('layouts.app')

@section('content')
    @while(have_posts()) @php the_post() @endphp
    @include('pages.content-home')
    @endwhile
@endsection
