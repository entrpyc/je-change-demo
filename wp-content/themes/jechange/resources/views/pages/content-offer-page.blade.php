

@if($data['page_build'])
    @foreach($data['page_build'] as $itteration => $row)
        @if($row['acf_fc_layout'] == 'add_hero_orange_block')
            @include('blocks.block-hero-orange', ['type' => 'hero'])
        @endif

        @if($row['acf_fc_layout'] == 'add_tile_block')
            @include('blocks.block-tile')
        @endif

        @if($row['acf_fc_layout'] == 'add_featured_articles_block')
            @include('blocks.block-featured-articles')
        @endif

        @if($row['acf_fc_layout'] == 'add_free_text_block')
            @include('blocks.block-free-text')
        @endif

        @if($row['acf_fc_layout'] == 'add_offers_slider_block')
            @include('blocks.block-offers-slider')
        @endif

        @if($row['acf_fc_layout'] == 'add_opinion_block')
            @include('blocks.block-opinion')
        @endif

        @if($row['acf_fc_layout'] == 'add_recommend_block')
            @include('blocks.block-recommend')
        @endif

        @if($row['acf_fc_layout'] == 'add_two_column_boxes')
            @include('blocks.block-two-column-boxes')
        @endif

        @if($row['acf_fc_layout'] == 'add_useful_block')
            @include('blocks.block-useful')
        @endif

        @if($row['acf_fc_layout'] == 'add_iframe_block')
            @include('blocks.block-iframe')
        @endif

        @if($row['acf_fc_layout'] == 'add_chess_section_block')
            @include('blocks.block-chess-sections')
        @endif

        @if($row['acf_fc_layout'] == 'add_link_icons_block')
            @include('blocks.block-link-icons')
        @endif

        @if($row['acf_fc_layout'] == 'add_small_card_block')
            @include('blocks.block-small-card')
        @endif

    @endforeach
@endif



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
    @php
        $fields = get_fields($post->ID);
        $provider = get_post($fields['provider_id']);
        $providerTitle = $provider->post_title;
        $providerLogo = get_field('provider_logo', $fields['provider_id']);
    @endphp

    {{-- <pre>@dump($fields)</pre> --}}
    <div class="offer">
        <h2>{{ $fields['title'] }}</h2>
        <div class="offer-wrapper">
            <div class="offer-row">
                <div class="offer-provider middle-center">
                    @if($providerLogo['url'])
                    <div>
                        <a href="{{ get_permalink($provider) }}">
                            <img src="{{ $providerLogo['url'] }}" alt="{{ $providerTitle }} logo">
                        </a>
                    </div>
                    @endif
                    {{ $providerTitle }}
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
