import 'bootstrap'
import Swiper from 'swiper';
import { Navigation, Autoplay } from 'swiper/modules';

window.addEventListener('DOMContentLoaded', function() {

    initSwiper()
    backToTop()

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

function initSwiper() {
    const swiper = new Swiper('.swiper', {
        loop: true,
        spaceBetween: 30,
        slidesPerView: 4,
        modules: [ Navigation, Autoplay ],
        // autoplay: {
        //     delay: 3000,
        //     disableOnInteraction: false,
        // },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        }
    })
}

function backToTop() {
    const backToTop = document.querySelector('#back-to-top')
    
    if (!backToTop) return
    
    backToTop.addEventListener('click', () => {
        window.scrollTo(0, 0)
    })
    
    window.addEventListener('scroll', () => {
        let scrollPosition = window.scrollY || document.documentElement.scrollTop
        let viewportHeight = window.innerHeight

        if (scrollPosition >= viewportHeight) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    })
}