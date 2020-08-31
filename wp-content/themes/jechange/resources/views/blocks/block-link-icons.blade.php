<section class="link-icons">
    <div class="container">
        @if($row['link_icons_title'])
        <h2>{{ $row['link_icons_title'] }}</h2>
        @endif
        <div class="icons-container flex jc-center ai-center @if(in_array('Orange Color', $row['icons_special']))orange-color @endif">
            @if($row['link_icons_repeater'])
            @foreach($row['link_icons_repeater'] as $item)
                <div class="icon-block flex flex-column jc-center ai-center relative">
                    <img src="{{ $item['icon'] }}" alt="">
                    <a href="{{ $item['link'] }}">{{ $item['title'] }}</a>
                    <a href="{{ $item['link'] }}" class="block-link"></a>
                </div>
            @endforeach
            @endif
        </div>
    </div>
</section>