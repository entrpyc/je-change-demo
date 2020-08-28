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
        <div class="container-list flex">
          <div class="left flex flex-column">
            <div class="icon-list flex flex-column">
              <div class="icon-block flex ai-center">
                <img class="svg" src="@asset('images/svg-icons/light-bulb.svg')" alt="">
                <div class="text">
                  <a href="https://www.jechange.fr/energie/electricite">Électricité moins chère <span><a href="https://www.jechange.fr/energie/electricite/comparatif">> Voir les offres d'électricité</a></span></a>
                </div>
              </div>
              <div class="icon-block flex ai-center">
                <img class="svg" src="@asset('images/svg-icons/flame.svg')" alt="">
                <div class="text">
                  <a href="https://www.jechange.fr/energie/gaz">Gaz moins cher <span><a href="https://www.jechange.fr/energie/gaz/comparatif">> Voir les offres de gaz</a></span></a>
                </div>
              </div>
              <div class="icon-block flex ai-center">
                <img class="svg" src="@asset('images/svg-icons/battery.svg')" alt="">
                <div class="text">
                  <a href="https://www.jechange.fr/energie/duale">Duale (électricité et gaz) <span><a href="https://www.jechange.fr/energie/duale/comparatif">> Voir les offres électricité et gaz</a></span></a>
                </div>
              </div>
            </div>
            <div class="buttons">
              <div class="greeen-button flex jc-start"><a href="https://www.jechange.fr/energie/electricite/comparateur">Comparer les offres d'énergie</a></div>
              <div class="yellow-button flex jc-start"><a href="https://www.jechange.fr/services/depannage">Assistance Pannes</a></div>
            </div>
          </div>
          <div class="right flex flex-column">
            <div class="round-corners flex flex-column ai-start">
              <div class="button"><a href="https://www.jechange.fr/energie/news">L'<b>actualité</b> énergie</a></div>
              <div class="button"><a href="https://www.jechange.fr/energie/guides">Les <b>guides</b> énergie</a></div>
              <div class="button"><a href="https://www.jechange.fr/energie/fournisseurs">Les <b>fournisseurs d'énergie</b></a></div>
            </div>
            <div class="description flex flex-column">
              <p>Nos services</p>
              <div class="block">
                <p><a href="https://www.jechange.fr/energie/electricite/guides/resilier-contrat-electricite-4813">Résilier son contrat énergie</a></p>
                <p><a href="https://www.jechange.fr/energie/simulateur">Estimer sa consommation</a></p>
                <p><a href="https://www.jechange.fr/energie/fournisseurs/edf/ouverture-compteur">Ouverture compteur électrique</a></p>
              </div>
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
