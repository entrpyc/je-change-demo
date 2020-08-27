{{ $row['slider_title'] }}
{{ $row['slider_subtitle'] }}
@if($row['slider_offers_relation'])
    @foreach($row['slider_offers_relation'] as $offer)

    @endforeach
@endif