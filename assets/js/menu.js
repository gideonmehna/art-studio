jQuery(document).ready(function($) {
    
    // Menu toggle functionality
    $('#primary-menu-trigger').click(function() {
        var $trigger = $(this);
        var $menu = $('#art-studio-navigation');
        
        $trigger.toggleClass('active');
        
        if ($menu.hasClass('show')) {
            $menu.removeClass('show');
            setTimeout(function() {
                $menu.hide();
            }, 300);
        } else {
            $menu.show();
            setTimeout(function() {
                $menu.addClass('show');
            }, 10);
        }
    });
    
    // Close menu when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#art-studio-custom-menu').length) {
            $('#art-studio-navigation').removeClass('show');
            $('#primary-menu-trigger').removeClass('active');
            setTimeout(function() {
                $('#art-studio-navigation').hide();
            }, 300);
        }
    });
    
    // Admin functionality (only if user is admin)
    if (artStudioMenu.is_admin) {
        
        // Edit menu item
        $(document).on('click', '.edit-menu-item', function() {
            var index = $(this).data('index');
            var $menuItem = $('.menu-item[data-index="' + index + '"]');
            var $link = $menuItem.find('a');
            var isHome = $link.find('.menu-home-icon').length > 0;
            
            $('#edit-item-index').val(index);
            $('#item-label').val(isHome ? 'Home' : $link.text().trim());
            $('#item-url').val($link.attr('href'));
            $('#item-is-home').prop('checked', isHome);
            
            $('#menu-item-modal').show();
        });
        
        // Delete menu item
        $(document).on('click', '.delete-menu-item', function() {
            if (confirm('Are you sure you want to delete this menu item?')) {
                var index = $(this).data('index');
                deleteMenuItem(index);
            }
        });
        
        // Add new menu item
        $('#add-menu-item').click(function() {
            $('#edit-item-index').val('');
            $('#item-label').val('');
            $('#item-url').val('');
            $('#item-is-home').prop('checked', false);
            $('#menu-item-modal').show();
        });
        
        // Close modal
        $('.menu-modal-close, .cancel-edit').click(function() {
            $('#menu-item-modal').hide();
        });
        
        // Close modal when clicking outside
        $('#menu-item-modal').click(function(e) {
            if (e.target === this) {
                $(this).hide();
            }
        });
        
        // Save menu item form
        $('#menu-item-form').submit(function(e) {
            e.preventDefault();
            
            var index = $('#edit-item-index').val();
            var label = $('#item-label').val();
            var url = $('#item-url').val();
            var isHome = $('#item-is-home').is(':checked');
            
            var menuItem = {
                label: label,
                url: url,
                is_home: isHome
            };
            
            if (index === '') {
                // Add new item
                addMenuItem(menuItem);
            } else {
                // Update existing item
                updateMenuItem(index, menuItem);
            }
            
            $('#menu-item-modal').hide();
        });
        
        // Make menu sortable for reordering
        $('#art-studio-primary-menu').sortable({
            items: '.menu-item',
            placeholder: 'ui-sortable-placeholder',
            helper: 'clone',
            start: function(e, ui) {
                ui.placeholder.height(ui.item.height());
            },
            update: function(e, ui) {
                // Update data-index attributes after sorting
                $('#art-studio-primary-menu .menu-item').each(function(index) {
                    $(this).attr('data-index', index);
                    $(this).find('.edit-menu-item, .delete-menu-item').attr('data-index', index);
                });
            }
        });
        
        // Save menu order
        $('#save-menu-order').click(function() {
            saveMenuOrder();
        });
    }
    
    // Helper functions for admin functionality
    function addMenuItem(menuItem) {
        var menuItems = getCurrentMenuItems();
        menuItems.push(menuItem);
        saveMenuItems(menuItems);
    }
    
    function updateMenuItem(index, menuItem) {
        var menuItems = getCurrentMenuItems();
        menuItems[index] = menuItem;
        saveMenuItems(menuItems);
    }
    
    function deleteMenuItem(index) {
        var menuItems = getCurrentMenuItems();
        menuItems.splice(index, 1);
        saveMenuItems(menuItems);
    }
    
    function getCurrentMenuItems() {
        var menuItems = [];
        $('.menu-item[data-index]').each(function() {
            var $item = $(this);
            var $link = $item.find('a');
            var isHome = $link.find('.menu-home-icon').length > 0;
            
            menuItems.push({
                label: isHome ? 'Home' : $link.text().trim(),
                url: $link.attr('href'),
                is_home: isHome
            });
        });
        return menuItems;
    }
    
    function saveMenuOrder() {
        var menuItems = getCurrentMenuItems();
        saveMenuItems(menuItems);
    }
    
    function saveMenuItems(menuItems) {
        $.post(artStudioMenu.ajax_url, {
            action: 'save_menu_items',
            menu_items: JSON.stringify(menuItems),
            nonce: artStudioMenu.nonce
        }, function(response) {
            if (response.success) {
                // Reload the page to show updated menu
                location.reload();
            } else {
                alert('Error saving menu: ' + response.data);
            }
        }).fail(function() {
            alert('Error: Could not save menu items');
        });
    }
    
    // Keyboard accessibility
    $('#primary-menu-trigger').keydown(function(e) {
        if (e.which === 13 || e.which === 32) { // Enter or Space
            e.preventDefault();
            $(this).click();
        }
    });
    
    // Add keyboard navigation to menu items
    $('.art-studio-menu a').keydown(function(e) {
        if (e.which === 9) { // Tab key
            var $items = $('.art-studio-menu a:visible');
            var currentIndex = $items.index(this);
            
            if (e.shiftKey) {
                // Shift+Tab - go to previous item
                if (currentIndex === 0) {
                    e.preventDefault();
                    $('#primary-menu-trigger').focus();
                }
            } else {
                // Tab - go to next item
                if (currentIndex === $items.length - 1) {
                    e.preventDefault();
                    $('#primary-menu-trigger').focus();
                    $('#art-studio-navigation').removeClass('show');
                    $('#primary-menu-trigger').removeClass('active');
                }
            }
        }
        
        if (e.which === 27) { // Escape key
            $('#art-studio-navigation').removeClass('show');
            $('#primary-menu-trigger').removeClass('active').focus();
        }
    });
    
    // Add smooth scrolling for anchor links
    $('.art-studio-menu a[href*="#"]').click(function(e) {
        var href = $(this).attr('href');
        var target = href.indexOf('#') >= 0 ? href.substr(href.indexOf('#')) : '';
        
        if (target && $(target).length) {
            e.preventDefault();
            
            // Close menu first
            $('#art-studio-navigation').removeClass('show');
            $('#primary-menu-trigger').removeClass('active');
            
            // Smooth scroll to target
            $('html, body').animate({
                scrollTop: $(target).offset().top - 100
            }, 800);
        }
    });
    
    // Add loading state for menu actions
    function showLoading() {
        if ($('.menu-loading').length === 0) {
            $('<div class="menu-loading">Saving...</div>').appendTo('#art-studio-custom-menu');
        }
    }
    
    function hideLoading() {
        $('.menu-loading').remove();
    }
    
    // Update saveMenuItems to show loading
    var originalSaveMenuItems = saveMenuItems;
    saveMenuItems = function(menuItems) {
        showLoading();
        
        $.post(artStudioMenu.ajax_url, {
            action: 'save_menu_items',
            menu_items: JSON.stringify(menuItems),
            nonce: artStudioMenu.nonce
        }, function(response) {
            hideLoading();
            if (response.success) {
                // Show success message briefly
                $('<div class="menu-success">Menu saved!</div>').appendTo('#art-studio-custom-menu').delay(2000).fadeOut();
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                alert('Error saving menu: ' + response.data);
            }
        }).fail(function() {
            hideLoading();
            alert('Error: Could not save menu items');
        });
    };
    
    // Add drag and drop functionality for mobile
    var touchStartY = 0;
    var touchEndY = 0;
    
    $('#art-studio-navigation').on('touchstart', function(e) {
        touchStartY = e.originalEvent.touches[0].clientY;
    });
    
    $('#art-studio-navigation').on('touchend', function(e) {
        touchEndY = e.originalEvent.changedTouches[0].clientY;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchEndY < touchStartY - 50) {
            // Swipe up - close menu
            $('#art-studio-navigation').removeClass('show');
            $('#primary-menu-trigger').removeClass('active');
        }
    }
    
    // Auto-hide menu after period of inactivity
    var menuTimeout;
    
    function resetMenuTimeout() {
        clearTimeout(menuTimeout);
        menuTimeout = setTimeout(function() {
            if ($('#art-studio-navigation').hasClass('show')) {
                $('#art-studio-navigation').removeClass('show');
                $('#primary-menu-trigger').removeClass('active');
            }
        }, 30000); // 30 seconds
    }
    
    $('#art-studio-custom-menu').on('mouseenter mousemove click', function() {
        resetMenuTimeout();
    });
    
    // Initialize timeout when menu opens
    $('#primary-menu-trigger').click(function() {
        if ($('#art-studio-navigation').hasClass('show')) {
            resetMenuTimeout();
        }
    });
    
});