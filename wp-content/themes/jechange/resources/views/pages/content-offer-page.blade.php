@include('pages.content-page-build')

<section class="offers-filters dark-bg">
    <div class="container">
        <p class="title">Les meilleures offres Internet</p>
        <div class="fit">
            <div class="name">Triez les résultats</div>
            <div class="filters">
                <form data-submit-on-change method="get" action="<?php the_permalink(); ?>">
                    @include('partials.filters.'.$data['type'])
                    <button class="hidden" type="submit"></button>
                </form>
            </div>
        </div>
    </div>
</section>
<section class="offers-listing dark-bg offset-bottom">
    <div class="container">
        <table>
            <caption>Classement réalisé par prix croissant</caption>
            <thead>
                <tr>
                    <th>FAI</th>
                    <th>Offre</th>
                    <th>Tarif</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['posts'] as $postObj)
                @php
                    $fields = get_fields($postObj->ID);
                    $provider = get_post($fields['provider_id']);
                    $providerTitle = $provider->post_title;
                    $providerLogo = get_field('provider_logo', $fields['provider_id']);
                @endphp
                <tr>
                    <td class="logo">
                        <div class="flex flex-column ai-center relative">
                            @if($providerLogo['url'])
                            <img src="{{ $providerLogo['url'] }}" alt="{{ $providerTitle }} logo">
                            @endif
                            {{ $providerTitle }}
                            <a class="block-link" href="{{ get_permalink($provider) }}"></a>
                        </div>
                    </td>
                    <td class="description">
                        <div class="flex flex-column">
                            {!! $fields['pictograms'] !!}
                            {!! $fields['description'] !!}
                            <ul>
                                @foreach($fields['features'] as $feature)
                                <li>{{ $feature['filter_text'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </td>
                    <td class="price">
                        <div>
                            <p>
                                @if($fields['is_monthly'])
                                {{  number_format((float) $fields['price'], 2) }}
                                € / month
                                @else
                                € {{ number_format((float) $fields['price'], 2) }}
                                @endif
                            </p>
                        </div>
                    </td>
                    <td>
                        <div class="action">
                            @if($fields['call_me_back'])
                            <a class="green-button" href="tel:{{ str_replace(' ', '', trim($fields['call_center_phone'])) }}">
                                Me faire rappeler
                            </a>
                            @endif
                            <a class="orange-border-button" href="tel:{{ str_replace(' ', '', trim($fields['call_center_phone'])) }}">
                                {{ $fields['call_center_phone']}}
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>FAI</th>
                    <th>Offre</th>
                    <th>Tarif</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</section>