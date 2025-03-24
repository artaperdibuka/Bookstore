

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
document.addEventListener('DOMContentLoaded', () => {
    const promoVideo = document.getElementById('promoVideo');
    const videoControlBtn = document.getElementById('videoControlBtn');

    // Event listener për butonin Play/Pause
    videoControlBtn.addEventListener('click', () => {
        if (promoVideo.paused) {
            promoVideo.play();
            videoControlBtn.classList.remove('play');
            videoControlBtn.classList.add('pause');
        } else {
            promoVideo.pause();
            videoControlBtn.classList.remove('pause');
            videoControlBtn.classList.add('play');
        }
    });

    // Vendos klasën fillestare bazuar në gjendjen e videos
    if (promoVideo.paused) {
        videoControlBtn.classList.add('play');
    } else {
        videoControlBtn.classList.add('pause');
    }
});


const leftArrowShop = document.querySelector('.shop-left-arrow'),
    rightArrowShop = document.querySelector('.shop-right-arrow'),
    shopSlider = document.querySelector('.shop-slider'),
    boxContainer = document.querySelector('.shop-slider .box-container');

let currentPosition = 0;
const boxWidth = 20; // 20% për secilin libër
const visibleBooks = 5; // Numri i librave të dukshëm njëkohësisht
let totalBooks;
let autoSlideInterval;

const gapWidth = 2; // 2% për hapsirën midis box-eve
const totalWidthPerBox = boxWidth + gapWidth; // Gjerësia totale për secilin box duke përfshirë hapsirën

function updateSliderPosition(animate) {
    if (animate) {
        boxContainer.style.transition = 'transform 0.5s ease-in-out';
    } else {
        boxContainer.style.transition = 'none';
    }

    boxContainer.style.transform = `translateX(-${currentPosition * totalWidthPerBox}%)`;

    // Kontrollo për reset
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

// Funksioni për të inicializuar slider-in
function setupInfiniteSlider() {
    const originalBoxes = document.querySelectorAll('.shop-slider .box');
    totalBooks = originalBoxes.length;

    // Klono librat në fillim dhe në fund për të krijuar efektin e pafundësisë
    for (let i = 0; i < visibleBooks; i++) {
        boxContainer.appendChild(originalBoxes[i].cloneNode(true)); // Klono në fund
        boxContainer.insertBefore(originalBoxes[totalBooks - 1 - i].cloneNode(true), boxContainer.firstChild); // Klono në fillim
    }

    // Fshi çdo përzierje vizuale gjatë inicializimit
    boxContainer.style.opacity = '0'; // Fshihni përkohësisht slider-in

    // Vendos pozicionin fillestar pas përfundimit të klonimit
    setTimeout(() => {
        currentPosition = visibleBooks;
        updateSliderPosition(false); // Përditësoni pozicionin pa animacion

        // Rikthe slider-in me opacity 1 pas përfundimit të klonimit
        boxContainer.style.opacity = '1';
    }, 300); // Jepni pak kohë për të përfunduar klonimin dhe rregullimet
}


// Funksioni për të lëvizur slider-in djathtas
function scrollRightShop() {
    currentPosition++;
    updateSliderPosition(true);
}

// Funksioni për të lëvizur slider-in majtas
function scrollLeftShop() {
    currentPosition--;
    updateSliderPosition(true);
}

// Funksioni për të përditësuar pozicionin e slider-it
function updateSliderPosition(animate) {
    if (animate) {
        boxContainer.style.transition = 'transform 0.5s ease-in-out';
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

// Funksioni për të filluar lëvizjen automatike
function startAutoSlide() {
    autoSlideInterval = setInterval(scrollRightShop, 7000); // Lëvizje automatike çdo 7 sekonda
}

// Funksioni për të ndaluar lëvizjen automatike
function stopAutoSlide() {
    clearInterval(autoSlideInterval);
}

// Funksioni për të rivendosur lëvizjen automatike
function resetAutoSlide() {
    stopAutoSlide();
    startAutoSlide();
}

// Event Listeners për shigjetat
leftArrowShop.addEventListener('click', () => {
    scrollLeftShop();
    resetAutoSlide();
});

rightArrowShop.addEventListener('click', () => {
    scrollRightShop();
    resetAutoSlide();
});

// Event Listeners për lëvizjen automatike
shopSlider.addEventListener('mouseenter', stopAutoSlide);
shopSlider.addEventListener('mouseleave', startAutoSlide);

// Inicializimi i slider-it
setupInfiniteSlider();
startAutoSlide();

