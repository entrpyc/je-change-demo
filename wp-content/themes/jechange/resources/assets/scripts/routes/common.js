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

      if (!item.querySelector('.sub-menu'))
        return;

      item.querySelector('.sub-menu').classList.toggle('open')
    }

    // const modelFrame = document.querySelector('[data-model-frame]')

    // const modelGElements = document.querySelectorAll('[data-model-select] .g-1, [data-model-select] .g-2')
    // // const modelG2Elements = document.querySelectorAll('[data-model-select] .g-2')


    // modelGElements.forEach(el => {
    //   el.classList.contains('g-1') ? modelFrame.querySelector('.g-1-parent').appendChild(el) : modelFrame.querySelector('.g-2-parent').appendChild(el)
    // })

    // const modelList = ['model-1', 'model-2', 'model-3', 'model-4']
    // let createdModels = [];

    // modelFrame.querySelectorAll('.g-1-parent > li.g-1').forEach(el => {
    //   let key;
    //   modelList.forEach(model => {
    //     if(key)
    //       return

    //     key = el.classList.contains(model) ? model : false
    //   })

    //   if(!createdModels.includes(key) && key != false) {
    //     let modelBound = document.createElement('div')
    //     modelBound.className = key + ' parent-model'
    //     modelFrame.querySelector('.g-1-parent').appendChild(modelBound)

    //     createdModels.push(key)
    //   }

    //   if(key) {
    //     modelFrame.querySelector(`.g-1-parent > .parent-model.${key}`).appendChild(el)
    //   }
    // })

    // modelFrame.querySelectorAll('.g-2-parent > li.g-2').forEach(el => {
    //   let key;
    //   modelList.forEach(model => {
    //     if(key)
    //       return

    //     key = el.classList.contains(model) ? model : false
    //   })

    //   console.log(key)

    //   if(!createdModels.includes(key) && key != false) {
    //     let modelBound = document.createElement('div')
    //     modelBound.className = key + ' parent-model'
    //     modelFrame.querySelector('.g-2-parent').appendChild(modelBound)

    //     createdModels.push(key)
    //   }

    //   if(key) {
    //     modelFrame.querySelector(`.g-2-parent > .parent-model.${key}`).appendChild(el)
    //   }
    // })

  },
  finalize() {
    // JavaScript to be fired on all pages, after page specific JS is fired
  },
};
