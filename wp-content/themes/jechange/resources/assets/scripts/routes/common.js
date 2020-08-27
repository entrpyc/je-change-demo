export default {
  init() {
    const mainNavItems = document.querySelectorAll('nav ul:not(.sub-menu) > li')

    console.log(mainNavItems)

    mainNavItems.forEach(item => {
      item.addEventListener('mouseenter', () => {
        openMenu(item)
      })

      item.addEventListener('mouseleave', () => {
        openMenu(item)
      })
    })

    function openMenu(item) {

      if (!item.querySelector('.sub-menu'))
        return;

      item.querySelector('.sub-menu').classList.toggle('open')
    }
  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
