@if($row['hero_type'] == 'Large')
<section class="block hero-orange">
  <div class="container flex flex-column ai-center">
    @if($row['hero_title'])
      <h1>{{ $row['hero_title'] }}</h1>
    @endif
    @if($row['hero_text'])
      <div class="text">
        {!! $row['hero_text'] !!}
      </div>
    @endif
  
    <img style="max-width: 520px;" src="{{ $row['hero_image'] }}" alt="hero-image">
  
    <div class="hero-button vr">
      <a href="https://www.jechange.fr/tousmescontrats">Démarrer</a>
    </div>
  </div>
</section>
@else 
<section class="block hero-orange-regular">
  <div class="container flex">
    <div class="info">
      <h1>{{ $row['hero_title'] }}</h1>
      @if($row['hero_text'])
        <div class="text">
          {!! $row['hero_text'] !!}
        </div>
      @endif
      <div class="buttons-wrapper flex jc-start ai-stretch">
        <div class="green-btn">
          <a href="{{$row['hero_green_button_link']}}">@if($row['hero_green_button_icon'])<img src="{{$row['hero_green_button_icon']}}" alt="">@endif{{$row['hero_green_button_text']}}</a>
        </div>
        <div class="button-border"><a href="tel:+33800811911" class="flex flex-column ai-center jc-center">
          <div class="btn">Ou appelez directement le :<br><span class="num">0800 811 911</span></div>
          <span class="text">Appel gratuit</span></a></div>
      </div>
    </div>
    <div class="image">
      <img src="{{ $row['hero_image'] }}" alt="">
    </div>
  </div>
</section>
@endif