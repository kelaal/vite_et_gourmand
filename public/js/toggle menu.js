const toggleBtn = document.querySelector('.hero_navbar__toggle');
const menu = document.querySelector('.hero_navbar__menu');

toggleBtn.addEventListener('click', () => {
    menu.classList.toggle('is-open');
});