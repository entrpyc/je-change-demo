<section>
    <div class="container">
        Home
        <br>
        
        electricite <br>
        <a href="/energie/electricite/comparatif/">Votre fournisseur d’électricité est-il le moins cher ?</a><br>
        internet<br>
        <a href="/telecom/internet/comparatif/">Comparateur offres internet</a><br>
        mobile<br>
        <a href="/telecom/mobile/forfait-bloque/">Les 5 meilleurs forfaits bloqués</a><br>
        <a href="/telecom/mobile/forfaitillimite/">Les 5 meilleurs forfaits illimités</a><br>
        
        
        @if($data['page_build'])
            @foreach($data['page_build'] as $row)
        
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
        
            @endforeach
        @endif
    </div>
</section>