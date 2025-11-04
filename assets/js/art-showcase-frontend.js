jQuery(document).ready(function($) {
    $('.art-showcase-wrapper').each(function() {
        const $wrapper = $(this);
        const $scrollContainer = $wrapper.find('.art-showcase-scroll');
        const $grid = $wrapper.find('.art-showcase-grid');
        const $leftNav = $wrapper.find('.art-showcase-nav-left');
        const $rightNav = $wrapper.find('.art-showcase-nav-right');
        
        
        // Calculate scroll amount (width of one item + gap)
        const getScrollAmount = () => {
            const $items = $grid.find('.art-showcase-item');
            if ($items.length) {
                const totalWidth = $items.toArray().reduce((acc, item) => acc + $(item).outerWidth(true), 0);
                return totalWidth / $items.length; // Average width
            }
            return 280; // fallback
        };
        
        // Update navigation button states
        const updateNavButtons = () => {
            const scrollLeft = $scrollContainer.scrollLeft();
            const maxScroll = $scrollContainer[0].scrollWidth - $scrollContainer[0].clientWidth;
            
            $leftNav.toggleClass('disabled', scrollLeft <= 0);
            $rightNav.toggleClass('disabled', scrollLeft >= maxScroll);
        };
        
        // Smooth scroll function
        const smoothScroll = (direction) => {

            const scrollAmount = getScrollAmount();
            const currentScroll = $scrollContainer.scrollLeft();
            const targetScroll = direction === 'left' 
                ? currentScroll - scrollAmount 
                : currentScroll + scrollAmount;
            
            $scrollContainer.animate({
                scrollLeft: targetScroll
            }, 300, 'swing', updateNavButtons);
        };
        
        // Left navigation click
        $leftNav.on('click', function(e) {
            e.preventDefault();
            if (!$(this).hasClass('disabled')) {
                smoothScroll('left');
            }
        });
        
        // Right navigation click
        $rightNav.on('click', function(e) {
            e.preventDefault();
            if (!$(this).hasClass('disabled')) {
                smoothScroll('right');
            }
        });
        
        // Handle manual scrolling
        $scrollContainer.on('scroll', function() {
            updateNavButtons();
        });
        
        // Initialize button states
        updateNavButtons();
        
        // Update on window resize
        let resizeTimeout;
        $(window).on('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(updateNavButtons, 100);
        });
        
        // Touch/swipe support for mobile
        let startX = 0;
        let scrollLeft = 0;
        let isDown = false;
        
        $scrollContainer.on('mousedown touchstart', function(e) {
            isDown = true;
            startX = (e.type === 'mousedown' ? e.pageX : e.touches[0].pageX) - $scrollContainer.offset().left;
            scrollLeft = $scrollContainer.scrollLeft();
            $scrollContainer.addClass('dragging');
        });
        
        $scrollContainer.on('mouseleave mouseup touchend', function() {
            isDown = false;
            $scrollContainer.removeClass('dragging');
        });
        
        $scrollContainer.on('mousemove touchmove', function(e) {
            if (!isDown) return;
            e.preventDefault();
            
            const x = (e.type === 'mousemove' ? e.pageX : e.touches[0].pageX) - $scrollContainer.offset().left;
            const walk = (x - startX) * 2;
            $scrollContainer.scrollLeft(scrollLeft - walk);
        });
        
        // Keyboard navigation
        $wrapper.on('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                smoothScroll('left');
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                smoothScroll('right');
            }
        });

        
        // Make wrapper focusable for keyboard navigation
        $wrapper.attr('tabindex', '0');
    });
    // Modal functionality
    $('.art-showcase-item').on('click', function() {
        const $modal = $(this).find('.art-modal');
        const $wrapper = $(this).closest('.art-showcase-wrapper');
        
        // Add active states
        $(this).addClass('modal-active');
        $wrapper.addClass('has-open-modal');
        
        // compute scrollbar width to avoid layout shift when hiding body scrollbar
        const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
        if (scrollbarWidth > 0) {
            $('body').css('padding-right', scrollbarWidth + 'px');
        } else {
            $('body').css('padding-right', '');
        }

        // Show modal and lock background scrolling
        $modal.fadeIn().addClass('modal-open');
        $('body').addClass('opened-modal');
    });
     // Prevent modal close when clicking modal content
    $('.art-modal-content').on('click', function(e) {
        e.stopPropagation();
    });
    // Close modal when clicking outside
    $('.art-modal').on('click', function(e) {
        if ($(e.target).hasClass('art-modal')) {
            $(this).fadeOut();
            $('body').removeClass('modal-open');
        }
    });

    function closeModal() {
        const $modal = $('.art-modal.modal-open');
        const $item = $modal.closest('.art-showcase-item');
        const $wrapper = $modal.closest('.art-showcase-wrapper');
        
        // Remove active states
        $item.removeClass('modal-active');
        $wrapper.removeClass('has-open-modal');
        
        // Hide modal
        $modal.fadeOut().removeClass('modal-open');
        $('body').removeClass('opened-modal');
        // remove any padding-right added when opening modal
        $('body').css('padding-right', '');
    }

    // Update existing close handlers to use closeModal()
    // $(document).on('click', '.art-showcase-modal-close', closeModal);
    $('.art-showcase-modal-close').on('click', closeModal);
    

    $('.art-modal').on('click', function(e) {
        if ($(e.target).hasClass('art-modal')) {
            closeModal();
        }
    });

    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            closeModal();
        }
    });
        
});