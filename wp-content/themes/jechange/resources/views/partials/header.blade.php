<header class="banner"> 
  <div class="container">
    <div class="ribbon-switch section dark-bg flex ai-center">
      <p>PARTICULIER</p>
      @include('icons.switch')
      <p>PROFESSIONNEL</p>
    </div>
    <div class="menu">
      <a class="brand" href="{{ home_url('/') }}">{{ get_bloginfo('name', 'display') }}</a>
      <nav class="nav-primary">
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
    </div>
  </div>
</header>
