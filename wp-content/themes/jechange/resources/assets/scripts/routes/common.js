export default {
  init() {
    const mainNavItems = document.querySelectorAll('nav ul:not(.sub-menu) > li')

    // console.log(mainNavItems)

    mainNavItems.forEach(item => {
      item.addEventListener('mouseenter', () => {
        openMenu(item)
      })

      item.addEventListener('mouseleave', () => {
        openMenu(item)
      })
    })

    function openMenu(item) {

      if (!item.querySelector('.dropdown'))
        return;

      item.querySelector('.dropdown').classList.toggle('open')
    }

    console.log(document.querySelector('.mega-menu.nth-2'))
    console.log(document.querySelector('[data-get="nth-2"]'))
    document.querySelector('.mega-menu.nth-2').appendChild(document.querySelector('[data-get="nth-2"]'))

    // img to svg
    $('img.svg').each(function () {
      var $img = jQuery(this);
      var imgID = $img.attr('id');
      var imgClass = $img.attr('class');
      var imgURL = $img.attr('src');
      $.get(imgURL, function (data) {
        var $svg = jQuery(data).find('svg');
        if (typeof imgID !== 'undefined') {
          $svg = $svg.attr('id', imgID);
        }
        if (typeof imgClass !== 'undefined') {
          $svg = $svg.attr('class', imgClass + ' replaced-svg');
        }
        $svg = $svg.removeAttr('xmlns:a');
        $img.replaceWith($svg);
      }, 'xml');
    });
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
