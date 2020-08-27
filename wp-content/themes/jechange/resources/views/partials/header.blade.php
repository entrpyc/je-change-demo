<header class="banner"> 
  <div class="container">
    <div class="ribbon-switch section dark-bg flex ai-center">
      <p>PARTICULIER</p>
      {{-- @include('partials.icons.switch') --}}
      <p><a href="">PROFESSIONNEL</a></p>
    </div>
    <div class="menu flex">
      <div class="brand">
        <a href="{{ home_url('/') }}"><img src="@asset('images/logo-jechange.png')" alt=""></a>
      </div>
      <nav class="nav-primary">
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
    </div>
  </div>
</header>
