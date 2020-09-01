export default {
  init() {
    const mainNavItems = document.querySelectorAll('nav ul:not(.sub-menu) > li')

    // console.log(mainNavItems)

    window.onload = () => {
      mainNavItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
          openMenu(item, 'enter')
        })
  
        item.addEventListener('mouseleave', () => {
          openMenu(item, 'leave')
        })
      })
    }

    function openMenu(item, event) {

      if (!item.querySelector('.dropdown'))
        return;

      event == 'leave' ? item.querySelector('.dropdown').classList.remove('open') :
      item.querySelector('.dropdown').classList.add('open')
    }

    document.querySelector('.mega-menu.nth-2').appendChild(document.querySelector('[data-get="nth-2"]'))
    document.querySelector('.mega-menu.nth-3').appendChild(document.querySelector('[data-get="nth-3"]'))

    let itt = 0;
    document.querySelectorAll('[data-submit-on-change]').forEach(dynamicForm => {
      dynamicForm.dataset.submitOnChange = itt++

      dynamicForm.querySelectorAll('input, label').forEach(input => {
        input.addEventListener('change', () => {
          dynamicForm.submit()
        })
      })
    })


    // img to svg
    $(window).on('load', () => {
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
        // console.log($img)
        // $img.addClass('visible')
      });
    })
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
