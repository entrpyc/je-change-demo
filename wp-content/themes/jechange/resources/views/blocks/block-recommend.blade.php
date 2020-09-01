<section class="recommended dark-bg">
    <div class="container">
        <h2>{{ $row['recommend_title'] }}</h2>
        <div class="listing flex jc-center">
            @foreach($row['recommend_repeater'] as $block)
                <div class="block flex flex-column ai-center">
                    <img src="{{$block['image']}}" alt="">
                    <p class="title">{{$block['title']}}</p>
                    <div class="bold-text">{{$block['bold_text']}}</div>
                    <div class="free-text">
                        {!! $block['content'] !!}
                    </div>
                    @foreach($block['buttons'] as $button)

                    @if($button['acf_fc_layout'] != 'phone_button')
                        <div class="button">
                            <a class="{{$button['acf_fc_layout']}}" 
                            href="{{$button['link']}}">{{$button['text']}}</a>
                        </div>
                    @else
                        <div class="button button-border"><a href="{{$button['phone']}}" class="flex flex-column ai-center jc-center">
                            <div class="btn">{{$button['text']}}<br><span class="num">{{$button['phone']}}</span></div>
                            <span class="text">{{$button['semi']}}</span></a>
                        </div>
                    @endif

                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</section>