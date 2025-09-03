jQuery(document).ready(function($) {
    let currentPage = 1;
    let currentFilters = {
        emotion: '',
        artist: '',
        age_min: '',
        age_max: ''
    };
    let isLoading = false;
    
    // Filter toggle functionality
    $('.filter-toggle').on('click', function() {
        const target = $(this).data('target');
        const filterElement = $('.' + target + '-filter');
        const isVisible = filterElement.is(':visible');
        
        // Hide all filter dropdowns
        $('.artist-filter, .age-filter').hide();
        $('.filter-toggle').text(function(i, text) {
            return text.replace('▲', '▼');
        });
        
        // Show/hide current filter
        if (!isVisible) {
            filterElement.show();
            $(this).text($(this).text().replace('▼', '▲'));
        }
    });
    
    // Artist filter
    $('.artist-filter').on('change', function() {
        const selectedArtist = $(this).val();
        currentFilters.artist = selectedArtist;
        
        // Update button text
        const buttonText = selectedArtist ? selectedArtist : 'All Artists';
        $('[data-filter="artist"]').text(buttonText);
        
        // Hide dropdown
        $(this).hide();
        $('.filter-toggle[data-target="artist"]').text('Artist ▼');
        
        // Apply filter
        applyFilters();
    });
    
    // Age filter
    $('.apply-age-filter').on('click', function() {
        const minAge = $('.age-min').val();
        const maxAge = $('.age-max').val();
        
        currentFilters.age_min = minAge;
        currentFilters.age_max = maxAge;
        
        // Update button text
        let buttonText = 'All Ages';
        if (minAge && maxAge) {
            buttonText = `Ages ${minAge}-${maxAge}`;
        } else if (minAge) {
            buttonText = `Ages ${minAge}+`;
        } else if (maxAge) {
            buttonText = `Ages up to ${maxAge}`;
        }
        
        $('[data-filter="age"]').text(buttonText);
        
        // Hide dropdown
        $('.age-filter').hide();
        $('.filter-toggle[data-target="age"]').text('Age ▼');
        
        // Apply filter
        applyFilters();
    });
    
    // Emotion filter
    $('.emotion-btn').on('click', function() {
        const emotion = $(this).data('emotion');
        currentFilters.emotion = emotion;
        
        // Update active state
        $('.emotion-btn').removeClass('active');
        $(this).addClass('active');
        
        // Apply filter
        applyFilters();
    });
    
    // Apply filters function
    function applyFilters() {
        if (isLoading) return;
        
        isLoading = true;
        currentPage = 1;
        
        // Show loading state
        $('#art-grid').html('<div class="loading">Loading...</div>');
        
        $.ajax({
            url: artGalleryAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'art_studio_filter_art',
                filters: currentFilters,
                nonce: artGalleryAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#art-grid').html(response.data.html);
                    
                    // Update load more button
                    if (response.data.has_more) {
                        if ($('.load-more-container').length === 0) {
                            $('.art-grid').after('<div class="load-more-container"><button id="load-more-btn" class="load-more-btn">Load More Artwork</button></div>');
                        }
                        $('#load-more-btn').show();
                    } else {
                        $('.load-more-container').remove();
                    }
                }
                isLoading = false;
            },
            error: function() {
                $('#art-grid').html('<div class="error">Error loading artwork. Please try again.</div>');
                isLoading = false;
            }
        });
    }
    
    // Load more functionality
    $(document).on('click', '#load-more-btn', function() {
        if (isLoading) return;
        
        isLoading = true;
        currentPage++;
        
        const $button = $(this);
        const originalText = $button.text();
        $button.text(artGalleryAjax.loading_text);
        
        $.ajax({
            url: artGalleryAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'art_studio_load_more_art',
                page: currentPage,
                filters: currentFilters,
                nonce: artGalleryAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#art-grid').append(response.data.html);
                    
                    if (!response.data.has_more) {
                        $button.text(artGalleryAjax.no_more_text);
                        setTimeout(() => {
                            $('.load-more-container').fadeOut();
                        }, 2000);
                    } else {
                        $button.text(originalText);
                    }
                }
                isLoading = false;
            },
            error: function() {
                $button.text(originalText);
                isLoading = false;
            }
        });
    });
    
    // Back to top functionality
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#back-to-top-btn').fadeIn();
        } else {
            $('#back-to-top-btn').fadeOut();
        }
    });
    
    $('#back-to-top-btn').on('click', function() {
        $('html, body').animate({
            scrollTop: 0
        }, 600);
        return false;
    });
    
    // Close dropdowns when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.filter-group').length) {
            $('.artist-filter, .age-filter').hide();
            $('.filter-toggle').text(function(i, text) {
                return text.replace('▲', '▼');
            });
        }
    });
    
    // Upload button placeholder
    $('.upload-btn').on('click', function() {
        alert('Upload functionality would be implemented here');
    });
});