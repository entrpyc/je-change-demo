@if($data['page_build'])
@foreach($data['page_build'] as $itteration => $row)
  @if($row['acf_fc_layout'] == 'add_hero_orange_block')
    @include('blocks.block-hero-orange')
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

  @if($row['acf_fc_layout'] == 'add_small_contact_block')
    @include('blocks.block-small-contact')
  @endif

@endforeach
@endif
