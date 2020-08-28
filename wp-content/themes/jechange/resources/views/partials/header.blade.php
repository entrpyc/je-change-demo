<header class="banner"> 
  <div class="container">
    <div class="ribbon-switch section dark-bg flex ai-center">
      <p>PARTICULIER</p>
      {{-- <img src="@asset('images/svg-icons/switch.svg')" alt=""> --}}
      <p><a href="">PROFESSIONNEL</a></p>
    </div>
    <div class="menu flex">
      <div class="brand">
        <a href="{{ home_url('/') }}"><img src="@asset('images/logo-jechange.png')" alt=""></a>
      </div>
      <div class="flex" data-model-frame style="display:none">
        <div class="g-1-parent flex flex-column"></div>
        <div class="g-2-parent flex flex-column"></div>
      </div>
      {{-- <div data-model-style>
        <div class="model-1"></div>
        <div class="model-2"></div>
        <div class="model-3"></div>
      </div> --}}
      <nav class="nav-primary" data-model-select>
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
    </div>
  </div>
</header>
