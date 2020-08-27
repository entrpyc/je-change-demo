{{ $row['block-recommend'] }}
@if($row['recommend_repeater'])
    @foreach($row['recommend_repeater'] as $item)
        {{ $item['title'] }}
        <img src="{{ $item['image'] }}" alt="img">
        {{ $item['bold_text'] }}
        {{ $item['orange_text'] }}
        {{ $item['small_text'] }}
        {{ $item['content'] }}
        {{ $item['green_button_text'] }}
        {{ $item['green_button_link'] }}
        {{ $item['phone_button_text'] }}
        {{ $item['phone_button_number'] }}
        {{ $item['yellow_button_text'] }}
        {{ $item['yellow_button_link'] }}
    @endforeach
@endif