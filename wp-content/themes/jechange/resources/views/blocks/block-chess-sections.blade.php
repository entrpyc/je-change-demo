{{ $row['chess_title'] }}

@if($row['chess_repeater'])
    @foreach($row['chess_repeater'] as $itt => $item)
        <div class="@if($itt%2 == 0) left @else right @endif">
            <img src="{{ $row['image'] }}" alt="img">
            {{ $item['title'] }}
            {!! $row['content'] !!}
            <a href="{{ $row['button_link'] }}">{{ $row['button_text'] }}</a>
        </div>
    @endforeach
@endif