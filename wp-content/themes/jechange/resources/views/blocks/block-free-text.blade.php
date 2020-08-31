{{-- @dump($row['insert_at_top'][]) --}}

<section class="free-text dark-bg">
  <div class="container">
    <div class="fit vr">
      @foreach($row['insert_at_top'] as $check)
        @if($check == 'Logo')
          <img src="@asset('images/logo-jechange.png')" alt="">
        @endif
      @endforeach
      {!! $row['free_text'] !!}
    </div>
  </div>
</section>