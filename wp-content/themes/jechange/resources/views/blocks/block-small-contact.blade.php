<section class="block phone-contact @if($row['contact_sm_orange_background']) orange-bg @endif">
  <div class="container flex flex-column ai-center">
    @if($row['contact_sm_title']) <h2>{{ $row['contact_sm_title'] }}</h2> @endif
    @if($row['contact_sm_content'])
    {!! $row['contact_sm_content'] !!}
    @endif
    @if($row['contact_sm_green_button_link'])
    <div class="button-green"><a href="{{ $row['contact_sm_green_button_link'] }}">{{ $row['contact_sm_green_button_text'] }}</a></div>
    @endif
    @if($row['contact_sm_regular_button_link'])
    <div class="button-border"><a href="{{ $row['contact_sm_regular_button_link'] }}" class="flex flex-column ai-center jc-center">
      <div class="btn">{{ $row['contact_sm_regular_button_text'] }}<br><span class="num">{{ $row['contact_sm_regular_button_number'] }}</span></div>
      @if($row['contact_sm_regular_button_semi'])<span class="text">{{ $row['contact_sm_regular_button_semi'] }}</span> @endif
    </a></div>
    @endif
  </div>
</section>