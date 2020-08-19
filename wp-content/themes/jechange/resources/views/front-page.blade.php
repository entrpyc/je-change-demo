@extends('layouts.app')

@section('content')
    @if(have_posts()) @php the_post() @endphp
    @include('pages.content-home')
    @endif
@endsection
