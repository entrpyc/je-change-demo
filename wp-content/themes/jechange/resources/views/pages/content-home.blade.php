
@if($data['page_build'])
  @foreach($data['page_build'] as $itteration => $row)
    @if($row['acf_fc_layout'] == 'add_hero_orange_block')
      @include('blocks.block-hero-orange', ['type' => 'hero'])
    @endif

    @if($itteration < 1)
    <section class="block phone-contact vr-g">
      <div class="container flex flex-column ai-center">
        <h2>Nos experts à votre service !</h2>
        <p>Les contrats ça nous connait : on trouve pour vous les meilleures offres, adaptées à vos besoins, et au meilleur prix !</p>
        <p><strong>Economisez plus de 300€ par an sur vos factures !</strong></p>
        <p>Service <strong>100 % gratuit !</strong></p>
        <div class="button-green"><a href="tel:+33800811911">1 expert vous rappelle</a></div>
        <div class="button-border"><a href="tel:+33800811911" class="flex flex-column ai-center jc-center">
          <div class="btn">Ou appelez directement le : <br><span class="num">0800 811 911</span></div>
          <span class="text">Appel gratuit</span>
        </a></div>
      </div>
    </section>
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
