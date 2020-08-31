
{{-- {{ $row['right_block_image'] }}
{{ $row['right_block_content'] }}
{{ $row['right_button_text'] }}
{{ $row['right_button_link'] }} --}}

<section class="block two-column">
  <div class="container flex flex-column vr">
    <div class="title"><h2>Ce que nous faisons pour vous</h2></div>
    @php $switch = 1; @endphp
    @foreach($row['rows'] as $row)
      <div class="row flex @if(++$switch % 2 == 0) flex-row @else flex-row-reverse @endif">
        <div class="image">
          <img src="{{ $row['block_image'] }}" alt="">
        </div>
        <div class="text">
          {!! $row['block_content'] !!}
          @if($row['button_text'])
            <div class="button-border-regular vr">
              <a href="{{ $row['button_link'] }}">{{ $row['button_text'] }}</a>
            </div>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</section>