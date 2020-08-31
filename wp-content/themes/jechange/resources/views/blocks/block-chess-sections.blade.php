<section class="block chess-section">
    <div class="container flex flex-column vr">
      <div class="title"><h2>{{ $row['chess_title'] }}</h2></div>
      @foreach($row['chess_repeater'] as $itt => $item)
        <div class="row flex @if(++$itt % 2 == 1) flex-row @else flex-row-reverse @endif">
          <div class="image">
            <img src="{{ $item['image'] }}" alt="">
          </div>
          <div class="text">
            <h3>{{ $item['title'] }}</h3>
            {!! $item['content'] !!}
            @if($item['button_text'])
              <div class="button-border-regular vr">
                <a href="{{ $item['button_link'] }}">{{ $item['button_text'] }}</a>
              </div>
            @endif
          </div>
        </div>
      @endforeach
    </div>
</section>