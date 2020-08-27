{{ $row['opinion_title'] }}
<img src="{{ $row['opinion_image'] }}" alt="alt-img">
@if($row['opinion_repeater'])
    @foreach($row['opinion_repeater'] as $opinion)

    @endforeach
@endif

{{ $row['opinion_more_button_text'] }}
{{ $row['opinion_more_button_link'] }}