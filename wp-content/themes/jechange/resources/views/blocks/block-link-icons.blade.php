{{ $row['link_icons_title'] }}

@if($row['link_icons_repeater'])
    @foreach($row['link_icons_repeater'] as $item)
        <img src="{{ $item['icon'] }}" alt="icon">
        <a href="{{ $item['link'] }}">{{ $item['title'] }}</a>
    @endforeach
@endif