<style>
    .container {
        margin: 10px auto;
        max-width: 100%;
        width: 960px;
    }
    .offer { 
        border: 1px solid #eee;
    }
    .offer-row { display: flex; justify-content: space-between}
    .btn-offer { display: block}
   
</style>

<div class="container">
    <form method="get" action="<?php the_permalink(); ?>" >
        @include('partials.filters.'.$data['type'])
        <br><button type="submit">Filter</button>
    </form>
</div>

@foreach($data['posts'] as $post)
@php($fields = get_fields($post->ID))

{{-- <pre>@dump($fields)</pre> --}}
<div class="offer container">
    <h2>{{ $fields['title'] }}</h2>
    <div class="offer-row">
        <div class="offer-provider">
            <img src="{{ $fields['provider_logo'] }}" alt="{{ $fields['provider_name'] }} logo">
            <br>{{ $fields['provider_name'] }} 
        </div>
        <div class="offer-description">
            {!! $fields['pictograms'] !!}
            {!! $fields['description'] !!}
        </div>
        <div class="offer-price">
            
            @if($fields['is_monthly'])
            {{  number_format((float) $fields['price'], 2) }} 
            € / month
            @else 
            € {{ number_format((float) $fields['price'], 2) }} 
            @endif
        </div>
        <div class="offer-contact">
            {{ $fields['call_center_phone']}}
            @if($fields['call_me_back'])
                <a href="tel:{{ str_replace(' ', '', trim($fields['call_center_phone'])) }}" class="btn btn-primary btn-offer">Call me back</a>
            @endif
        </div>
    </div>

    @foreach($fields['features'] as $feature) 
        <div>{{ $feature['filter_id'] }}:  {{ $feature['filter_text'] }}</div>
    @endforeach

</div>

@endforeach