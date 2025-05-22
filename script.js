

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


function startAutoSlide() {
    autoSlideInterval = setInterval(scrollRight, 7000); // Lëvizje automatike çdo 7 sekonda
}

function stopAutoSlide() {
    clearInterval(autoSlideInterval);
}

function resetAutoSlide() {
    stopAutoSlide();
    startAutoSlide();
}
slider.addEventListener('mouseenter', stopAutoSlide);
slider.addEventListener('mouseleave', startAutoSlide);


// Scroll event
slider.addEventListener('click', function(ev){
    if(ev.target === leftArrow) {
        scrollLeft();
    } else if (ev.target === rightArrow) {
        scrollRight();
    }
    resetTimer();
});



const leftArrowShop = document.querySelector('.shop-left-arrow'),
    rightArrowShop = document.querySelector('.shop-right-arrow'),
    shopSlider = document.querySelector('.shop-slider'),
    boxContainer = document.querySelector('.shop-slider .box-container');

let currentPosition = 0;
const boxWidth = 20; 
const visibleBooks = 5; 
let totalBooks;
let autoSlideInterval;

const gapWidth = 2;
const totalWidthPerBox = boxWidth + gapWidth; 

function updateSliderPosition(animate) {
    if (animate) {
        boxContainer.style.transition = 'transform 0.5s ease-in-out';
    } else {
        boxContainer.style.transition = 'none';
    }

    boxContainer.style.transform = `translateX(-${currentPosition * totalWidthPerBox}%)`;


    if (currentPosition >= totalBooks + visibleBooks) {
        setTimeout(() => {
            currentPosition = visibleBooks;
            boxContainer.style.transition = 'none';
            boxContainer.style.transform = `translateX(-${currentPosition * totalWidthPerBox}%)`;
        }, 500);
    } else if (currentPosition < visibleBooks) {
        setTimeout(() => {
            currentPosition = totalBooks + visibleBooks - 1;
            boxContainer.style.transition = 'none';
            boxContainer.style.transform = `translateX(-${currentPosition * totalWidthPerBox}%)`;
        }, 500);
    }
}


function setupInfiniteSlider() {
    const originalBoxes = document.querySelectorAll('.shop-slider .box');
    totalBooks = originalBoxes.length;

    for (let i = 0; i < visibleBooks; i++) {
        boxContainer.appendChild(originalBoxes[i].cloneNode(true));
        boxContainer.insertBefore(originalBoxes[totalBooks - 1 - i].cloneNode(true), boxContainer.firstChild); // Klono në fillim
    }

    boxContainer.style.opacity = '0'; 

    setTimeout(() => {
        currentPosition = visibleBooks;
        updateSliderPosition(false); 

        boxContainer.style.opacity = '1';
    }, 300); 
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
        boxContainer.style.transition = 'transform 0.5s ease-in-out';
    } else {
        boxContainer.style.transition = 'none';
    }

    boxContainer.style.transform = `translateX(-${currentPosition * boxWidth}%)`;

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

leftArrowShop.addEventListener('click', () => {
    scrollLeftShop();
    resetAutoSlide();
});

rightArrowShop.addEventListener('click', () => {
    scrollRightShop();
    resetAutoSlide();
});


shopSlider.addEventListener('mouseenter', stopAutoSlide);
shopSlider.addEventListener('mouseleave', startAutoSlide);


setupInfiniteSlider();
startAutoSlide();

