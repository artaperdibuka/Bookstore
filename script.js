

const header = document.querySelector('header');
function fixedNavbar(){
    header.classList.toggle('scroll', window.pageYOffset > 0)
}
fixedNavbar();
window.addEventListener('scroll', fixedNavbar);

let menu = document.querySelector('#menu-btn');
let userBtn = document.querySelector('#user-btn');

menu.addEventListener('click', function(){
    let nav = document.querySelector('.navbar');
    nav.classList.toggle('active');
})
userBtn.addEventListener('click', function(){
    let userBox = document.querySelector('.user-box');
    userBox.classList.toggle('active');
})
"use strict";
// Home page slider
const leftArrow = document.querySelector('.left-arrow .bxs-left-arrow'),
rightArrow = document.querySelector('.right-arrow .bxs-right-arrow'),
slider = document.querySelector('.slider');

// Scroll to right 
function scrollRight(){
    if(slider.scrollWidth - slider.clientWidth === slider.scrollLeft){
        slider.scrollTo({
            left: 0,
            behavior: "smooth"
        });
    } else {
        slider.scrollBy({
            left: window.innerWidth,
            behavior: "smooth"
        })
    }
}

// Scroll to left
function scrollLeft(){
    slider.scrollBy({
        left: -window.innerWidth,
        behavior: "smooth"
    })
}

let timerId = setInterval(scrollRight, 7000);

// Reset timer to scroll right
function resetTimer(){
    clearInterval(timerId);
    timerId = setInterval(scrollRight, 7000);
}

// Scroll event
slider.addEventListener('click', function(ev){
    if(ev.target === leftArrow){
        scrollLeft();
        resetTimer();
    }
})

slider.addEventListener('click', function(ev){
    if(ev.target === rightArrow){
        scrollRight();
        resetTimer();
    }
})

// Shop Slider
const leftArrowShop = document.querySelector('.shop-left-arrow .bxs-left-arrow'),
      rightArrowShop = document.querySelector('.shop-right-arrow .bxs-right-arrow'),
      shopSlider = document.querySelector('.shop-slider'),
      boxContainer = document.querySelector('.shop-slider .box-container');

let currentPosition = 0;
const boxWidth = 25; // 25% për secilin libër
const visibleBooks = 4; // Numri i librave të dukshëm njëkohësisht
let totalBooks;
let autoSlideInterval;

function setupInfiniteSlider() {
    const originalBoxes = document.querySelectorAll('.shop-slider .box');
    totalBooks = originalBoxes.length;

    // Klono librat në fillim dhe në fund
    for (let i = 0; i < visibleBooks; i++) {
        boxContainer.appendChild(originalBoxes[i].cloneNode(true));
        boxContainer.insertBefore(originalBoxes[totalBooks - 1 - i].cloneNode(true), boxContainer.firstChild);
    }

    // Vendos pozicionin fillestar
    currentPosition = visibleBooks;
    updateSliderPosition(false);
}

function scrollRightShop() {
    currentPosition++;
    updateSliderPosition(true);
}

function scrollLeftShop() {
    currentPosition--;
    updateSliderPosition(true);
}

function updateSliderPosition(animate) {
    if (animate) {
        boxContainer.style.transition = 'transform 0.5s ease';
    } else {
        boxContainer.style.transition = 'none';
    }
    
    boxContainer.style.transform = `translateX(-${currentPosition * boxWidth}%)`;

    // Kontrollo për reset
    if (currentPosition >= totalBooks + visibleBooks) {
        setTimeout(() => {
            currentPosition = visibleBooks;
            boxContainer.style.transition = 'none';
            boxContainer.style.transform = `translateX(-${currentPosition * boxWidth}%)`;
        }, 500);
    } else if (currentPosition < visibleBooks) {
        setTimeout(() => {
            currentPosition = totalBooks + visibleBooks - 1;
            boxContainer.style.transition = 'none';
            boxContainer.style.transform = `translateX(-${currentPosition * boxWidth}%)`;
        }, 500);
    }
}

function startAutoSlide() {
    autoSlideInterval = setInterval(scrollRightShop, 7000);
}

function stopAutoSlide() {
    clearInterval(autoSlideInterval);
}

function resetAutoSlide() {
    stopAutoSlide();
    startAutoSlide();
}

// Event Listeners
leftArrowShop.addEventListener('click', () => {
    scrollLeftShop();
    resetAutoSlide();
});

rightArrowShop.addEventListener('click', () => {
    scrollRightShop();
    resetAutoSlide();
});

// Mouse events për slider
shopSlider.addEventListener('mouseenter', stopAutoSlide);
shopSlider.addEventListener('mouseleave', startAutoSlide);

// Inicializimi
setupInfiniteSlider();
startAutoSlide();

