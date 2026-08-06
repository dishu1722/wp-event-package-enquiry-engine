document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.category-tab-item');
    const contentPanels = document.querySelectorAll('.category-panel-item');

    // 1. INITIALIZE SLICK SLIDER FOR ALL PACKAGE GALLERIES
    function initSlickSliders() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.slick) {
            jQuery('.package-gallery-slider').each(function () {
                if (!jQuery(this).hasClass('slick-initialized')) {
                    jQuery(this).slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        dots: true,
                        arrows: true,
                        adaptiveHeight: false,
                        infinite: true,
                        prevArrow: '<button type="button" class="slick-prev">&#10094;</button>',
                        nextArrow: '<button type="button" class="slick-next">&#10095;</button>'
                    });
                }
            });
        }
    }

    // Run slider initialization on page load
    initSlickSliders();

    // 2. HANDLE TAB CLICKS & REFRESH SLIDERS
    tabButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');

            tabButtons.forEach(btn => btn.classList.remove('active'));
            contentPanels.forEach(panel => panel.classList.remove('active'));

            this.classList.add('active');
            
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                targetPanel.classList.add('active');

                // Recalculate slider dimensions when tab becomes active
                setTimeout(function () {
                    if (typeof jQuery !== 'undefined' && jQuery.fn.slick) {
                        jQuery(targetPanel).find('.package-gallery-slider').slick('setPosition');
                    }
                }, 50);
            }
        });
    });

    // 3. HORIZONTAL SCROLL ARROWS FOR CATEGORIES
    const navWrapper = document.getElementById('categoryTabWrapper');
    const btnLeft = document.getElementById('scrollLeftArr');
    const btnRight = document.getElementById('scrollRightArr');

    if (navWrapper && btnLeft && btnRight) {
        btnLeft.addEventListener('click', () => { navWrapper.scrollBy({ left: -200, behavior: 'smooth' }); });
        btnRight.addEventListener('click', () => { navWrapper.scrollBy({ left: 200, behavior: 'smooth' }); });
    }

    // 4. SET FIRST TAB & FIRST PANEL ACTIVE BY DEFAULT
    if (tabButtons.length > 0 && contentPanels.length > 0) {
        tabButtons[0].classList.add('active');
        contentPanels[0].classList.add('active');
        
        // Trigger position refresh for first active tab
        setTimeout(function () {
            if (typeof jQuery !== 'undefined' && jQuery.fn.slick) {
                jQuery('.package-gallery-slider').slick('setPosition');
            }
        }, 100);
    }
});

jQuery(document).ready(function($) {
    function getUrlParam(param) {
        var results = new RegExp('[?&]' + param + '=([^&#]*)').exec(window.location.href);
        return results ? decodeURIComponent(results[1].replace(/\+/g, ' ')) : null;
    }

    var title    = getUrlParam('pkg_title');
    var subtitle = getUrlParam('pkg_sub');
    var price    = getUrlParam('pkg_price');
    var min      = getUrlParam('pkg_min');

    if (title) {
        // 1. Update text elements on the HTML card
        $('#displayPackageTitle').text(title);
        $('#displayPackageSub').text(subtitle || '');
        if (price) {
            $('#displayPackagePriceInline').text(price);
            $('#displayPackageBadgePrice').text(price);
        }
        if (min) {
            $('#displayPackageMin').text(min);
        }

        // 2. Set value on WPForms Field #19 so it gets submitted in form entries/emails
        $('#wpforms-8231-field_20').val(title + (subtitle ? ' - ' + subtitle : ''));
    }
});
