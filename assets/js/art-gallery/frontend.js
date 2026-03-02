jQuery(document).ready(function($) {
    let currentPage = 1;
    let currentFilters = {
        emotion: '',
        artist: '',
        age_min: '',
        age_max: ''
    };
    let isLoading = false;

    // Read the permanent block-level category set by the editor (e.g. 'pro', 'general', or '').
    // This is never shown to the user — it scopes the gallery to a specific category.
    const galleryContainer = document.querySelector('.art-gallery-container');
    const blockCategory = galleryContainer ? (galleryContainer.getAttribute('data-category') || '') : '';
    
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
                filters: Object.assign({}, currentFilters, { art_category: blockCategory }),
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
                filters: Object.assign({}, currentFilters, { art_category: blockCategory }),
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
    
    //
    // Add to your existing jQuery ready function
    // $('.art-item').on('click', function() {
    //     console.log("fired");
    //     const postId = $(this).data('post-id');
    //     $(`#modal-${postId}`).fadeIn();
    //     $('body').addClass('modal-open');
    // });

    $('.modal-close').on('click', function() {
        $(this).closest('.art-modal').fadeOut();
        $('body').removeClass('modal-open');
    });

    // Close modal when clicking outside
    $('.art-modal').on('click', function(e) {
        if ($(e.target).hasClass('art-modal')) {
            $(this).fadeOut();
            $('body').removeClass('modal-open');
        }
    });

    // Prevent modal close when clicking modal content
    $('.art-modal-content').on('click', function(e) {
        e.stopPropagation();
    });
    
    // ----
    // Function to get URL parameters
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Check for emotion filter in URL on page load
    function checkUrlFilters() {
        const emotionFilter = getUrlParameter('filter_emotion');
        if (emotionFilter) {
            // Find and click the corresponding emotion button
            $(`.emotion-btn[data-emotion="${emotionFilter}"]`).trigger('click');
            
            // Scroll to gallery section
            $('html, body').animate({
                scrollTop: $('.art-gallery-container').offset().top - 100
            }, 600);
        }
    }

    // Run on page load
    checkUrlFilters();

    // Update URL when filters change
    function updateUrl() {
        const newUrl = currentFilters.emotion ? 
            addQueryParam(window.location.href, 'filter_emotion', currentFilters.emotion) :
            removeQueryParam(window.location.href, 'filter_emotion');
            
        window.history.pushState({}, '', newUrl);
    }

    // Helper function to add/update query parameter
    function addQueryParam(url, key, value) {
        const re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        const separator = url.indexOf('?') !== -1 ? "&" : "?";
        if (url.match(re)) {
            return url.replace(re, '$1' + key + "=" + value + '$2');
        }
        else {
            return url + separator + key + "=" + value;
        }
    }

    // Helper function to remove query parameter
    function removeQueryParam(url, parameter) {
        const urlparts = url.split('?');   
        if (urlparts.length >= 2) {
            const prefix = encodeURIComponent(parameter) + '=';
            const pars = urlparts[1].split(/[&;]/g);

            for (let i = pars.length; i-- > 0;) {    
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {   
                    pars.splice(i, 1);
                }
            }

            return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
        }
        return url;
    }

    // Modify your existing emotion filter click handler
    $('.emotion-btn').on('click', function() {
        // Check if JavaScript is enabled and working
        if (document.querySelector('.art-gallery-container').getAttribute('data-has-js') === 'true') {
            // Prevent default link behavior only when JS is working
            e.preventDefault();
            
            const emotion = $(this).data('emotion');
            currentFilters.emotion = emotion;
            
            // Update active state
            $('.emotion-btn').removeClass('active');
            $(this).addClass('active');
            
            // Update URL without reload
            updateUrl();
            
            // Apply filter via AJAX
            applyFilters();
        }
        // If JS is not enabled, let the link work normally (PHP fallback)
    });

    // With these delegated event handlers:
    $(document).on('click', '.art-item', function() {
        const postId = $(this).data('post-id');
        $(`#modal-${postId}`).fadeIn();
        $('body').addClass('modal-open');
    });

    $(document).on('click', '.modal-close', function() {
        $(this).closest('.art-modal').fadeOut();
        $('body').removeClass('modal-open');
    });

    $(document).on('click', '.art-modal', function(e) {
        if ($(e.target).hasClass('art-modal')) {
            $(this).fadeOut();
            $('body').removeClass('modal-open');
        }
    });

    $(document).on('click', '.art-modal-content', function(e) {
        e.stopPropagation();
    });
});