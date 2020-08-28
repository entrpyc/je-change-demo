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
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
