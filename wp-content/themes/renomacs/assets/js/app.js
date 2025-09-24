import 'bootstrap'

window.addEventListener('DOMContentLoaded', function() {

    document.addEventListener('scroll', function() {
        shrinkNavigation();
    })
});

function shrinkNavigation() {
    const nav = document.querySelector('header');

    document.addEventListener('scroll', function() {
        if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });
}