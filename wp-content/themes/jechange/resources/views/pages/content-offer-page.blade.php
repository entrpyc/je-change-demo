<style>
    .container {
        margin: 10px auto;
        max-width: 100%;
        width: 1020px;
    }

    .offer {
        line-height: 150%;
        padding: 5px 15px;
        margin: 15px auto;
        background-color: #fcfcfc !important;
    }

    .offer,
    .offer-wrapper {
        border: 1px solid #eee;
        background-color: #fff;
    }

    .offer-row {
        display: flex;
        justify-content: space-between
    }

    .offer-row>div {
        width: 25%;
    }

    .btn-offer {
        display: block;
        margin: 0 auto;
        padding: 5px 15px;
        text-decoration: none;
        color: #fff;
        border-radius: 5px;
        background-color: #2a9ee3;
    }

    .offer-price {
        background-color: #fac261;
        font-size: 120%;
        font-weight: 500;
    }

    .middle-center {
        text-align: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .offer .features {
        background-color: #eee;
        padding: 5px 15px;
    }
</style>

<div class="container">
    <form method="get" action="<?php the_permalink(); ?>">
        @include('partials.filters.'.$data['type'])
        <br><button type="submit">Filter</button>
    </form>
    @foreach($data['posts'] as $post)
    @php($fields = get_fields($post->ID))

    {{-- <pre>@dump($fields)</pre> --}}
    <div class="offer">
        <h2>{{ $fields['title'] }}</h2>
        <div class="offer-wrapper">
            <div class="offer-row">
                <div class="offer-provider middle-center">
                    <div>
                    <img src="{{ $fields['provider_logo'] }}" alt="{{ $fields['provider_name'] }} logo">
                    </div>
                    {{ $fields['provider_name'] }}
                </div>
                <div class="offer-description">
                    {!! $fields['pictograms'] !!}
                    {!! $fields['description'] !!}
                </div>
                <div class="offer-price middle-center">

                    @if($fields['is_monthly'])
                    {{  number_format((float) $fields['price'], 2) }}
                    € / month
                    @else
                    € {{ number_format((float) $fields['price'], 2) }}
                    @endif
                </div>
                <div class="offer-contact middle-center">
                    {{ $fields['call_center_phone']}}
                    @if($fields['call_me_back'])
                    <a href="tel:{{ str_replace(' ', '', trim($fields['call_center_phone'])) }}"
                        class="btn btn-primary btn-offer">
                        Me faire rappeler
                    </a>
                    @endif
                </div>
            </div>
            <div class="features">
                <ul>
                    @foreach($fields['features'] as $feature)
                    <li>{{ $feature['filter_text'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endforeach
</div>