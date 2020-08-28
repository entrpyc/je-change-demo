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
      <div data-get="nth-2" class="dropdown">
        <div class="container-list flex flex-direction-column">
          <div class="icon-list flex flex-column">
            <div class="icon-block flex">
              <img src="" alt="">
              <div class="text">Électricité moins chère <span>> Voir les offres d'électricité</span></div>
            </div>
            <div class="icon-block flex">
              <img src="" alt="">
              <div class="text">Électricité moins chère <span>> Voir les offres d'électricité</span></div>
            </div>
            <div class="icon-block flex">
              <img src="" alt="">
              <div class="text">Électricité moins chère <span>> Voir les offres d'électricité</span></div>
            </div>
          </div>
          <div class="buttons">
            <div class="greeen-button"></div>
            <div class="yellow-button"></div>
          </div>
          <div class="break"></div>
          <div class="round-corners flex flex-column">
            <div class="button"></div>
            <div class="button"></div>
            <div class="button"></div>
          </div>
          <div class="description flex flex-column">
            <p>tititit</p>
            <div class="block">
              <p>askdjasldkjsa</p>
              <p>askdjasldkjsa</p>
              <p>askdjasldkjsa</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="nav-primary" data-model-select>
        @if (has_nav_menu('primary_navigation'))
          {!! wp_nav_menu(['theme_location' => 'primary_navigation', 'menu_class' => 'nav']) !!}
        @endif
      </nav>
    </div>
  </div>
</header>
