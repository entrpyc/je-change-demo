{{ $row['featured_articles_title'] }}
@if($row['featured_articles_repeater'])
    @foreach($row['featured_articles_repeater'] as $item)
        {{ $item['title'] }}
        @if($item['articles_relation'])
            @foreach($item['articles_relation'] as $article)
                {{ $article->post_title }}
                {{ get_permalink($article->ID) }}
            @endforeach
        @endif
    @endforeach
@endif