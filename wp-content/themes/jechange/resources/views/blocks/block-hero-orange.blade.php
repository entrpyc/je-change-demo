{{-- {{ $row['hero_title'] }}
<img src="{{ $row['hero_image'] }}" alt="hero-image">
{!! $row['hero_text'] !!}
{{ $row['hero_title'] }}
{{ $row['hero_green_button_icon'] }}
{{ $row['hero_green_button_text'] }}
{{ $row['hero_green_button_link'] }}
{{ $row['hero_button_text'] }}
{{ $row['hero_button_phone'] }}
{{ $row['hero_button_link'] }} --}}

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
  
    @if(isset($type))
      <div class="hero-button vr">
        <a href="https://www.jechange.fr/tousmescontrats">Démarrer</a>
      </div>
    @endif
  </div>
</section>