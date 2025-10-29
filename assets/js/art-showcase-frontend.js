jQuery(document).ready(function($) {
    $('.art-showcase-wrapper').each(function() {
        const $wrapper = $(this);
        const $scrollContainer = $wrapper.find('.art-showcase-scroll');
        const $grid = $wrapper.find('.art-showcase-grid');
        const $leftNav = $wrapper.find('.art-showcase-nav-left');
        const $rightNav = $wrapper.find('.art-showcase-nav-right');
        
        
        // Calculate scroll amount (width of one item + gap)
        const getScrollAmount = () => {
            const $firstItem = $grid.find('.art-showcase-item').first();
            if ($firstItem.length) {
                const itemWidth = $firstItem.outerWidth(true);
                return itemWidth;
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
        $(window).on('resize', function() {
            updateNavButtons();
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

        // Modal functionality
        $(document).on('click', '.art-showcase-item', function() {
            const $modal = $(this).find('.art-modal');
            const $wrapper = $(this).closest('.art-showcase-wrapper');
            
            // Add active states
            $(this).addClass('modal-active');
            $wrapper.addClass('has-open-modal');
            
            // Show modal
            $modal.fadeIn().addClass('modal-open');
            $('body').addClass('opened-modal');
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
        }

        // Update existing close handlers to use closeModal()
        $(document).on('click', '.modal-close', closeModal);

        $(document).on('click', '.art-modal', function(e) {
            if ($(e.target).hasClass('art-modal')) {
                closeModal();
            }
        });

        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                closeModal();
            }
        });
        
        // Make wrapper focusable for keyboard navigation
        $wrapper.attr('tabindex', '0');
    });
});