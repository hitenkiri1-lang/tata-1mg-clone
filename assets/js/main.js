/**
 * Main JavaScript File
 * Contains common JavaScript functions and AJAX calls
 */

$(document).ready(function() {
    
    // =====================================================
    // AJAX SEARCH FUNCTIONALITY
    // =====================================================
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(function() {
                $.ajax({
                    url: 'ajax/search_medicines.php',
                    method: 'GET',
                    data: { q: query },
                    success: function(response) {
                        $('#searchSuggestions').html(response).show();
                    }
                });
            }, 300);
        } else {
            $('#searchSuggestions').hide();
        }
    });
    
    // Hide suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-form').length) {
            $('#searchSuggestions').hide();
        }
    });
    
    // =====================================================
    // ADD TO CART FUNCTIONALITY
    // =====================================================
    $('.add-to-cart-btn').on('click', function(e) {
        e.preventDefault();
        const medicineId = $(this).data('medicine-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
        
        // Determine correct path based on current location
        const ajaxPath = window.location.pathname.includes('/user/') ? '../ajax/add_to_cart.php' : 'ajax/add_to_cart.php';
        
        $.ajax({
            url: ajaxPath,
            method: 'POST',
            data: { medicine_id: medicineId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update cart count
                    $('#cartCount').text(response.cart_count);
                    
                    // Show success message
                    showAlert('success', 'Medicine added to cart successfully!');
                    
                    // Reset button
                    button.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                } else {
                    showAlert('danger', response.message);
                    button.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred. Please try again.');
                button.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
            }
        });
    });
    
    // =====================================================
    // UPDATE CART QUANTITY
    // =====================================================
    $('.update-quantity').on('click', function() {
        const cartId = $(this).data('cart-id');
        const action = $(this).data('action');
        const quantityElement = $('#quantity-' + cartId);
        let currentQuantity = parseInt(quantityElement.text());
        
        if (action === 'increase') {
            currentQuantity++;
        } else if (action === 'decrease' && currentQuantity > 1) {
            currentQuantity--;
        } else {
            return;
        }
        
        // Determine correct path based on current location
        const ajaxPath = window.location.pathname.includes('/user/') ? '../ajax/update_cart.php' : 'ajax/update_cart.php';
        
        $.ajax({
            url: ajaxPath,
            method: 'POST',
            data: { 
                cart_id: cartId, 
                quantity: currentQuantity 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    quantityElement.text(currentQuantity);
                    location.reload(); // Reload to update totals
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Failed to update cart. Please try again.');
            }
        });
    });
    
    // =====================================================
    // REMOVE FROM CART
    // =====================================================
    $('.remove-from-cart').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to remove this item from cart?')) {
            return;
        }
        
        const cartId = $(this).data('cart-id');
        
        // Determine correct path based on current location
        const ajaxPath = window.location.pathname.includes('/user/') ? '../ajax/remove_from_cart.php' : 'ajax/remove_from_cart.php';
        
        $.ajax({
            url: ajaxPath,
            method: 'POST',
            data: { cart_id: cartId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert('danger', response.message);
                }
            },
            error: function() {
                showAlert('danger', 'Failed to remove item. Please try again.');
            }
        });
    });
    
    // =====================================================
    // FORM VALIDATION
    // =====================================================
    $('form').on('submit', function(e) {
        const form = $(this);
        let isValid = true;
        
        // Check required fields
        form.find('[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Email validation
        const emailField = form.find('input[type="email"]');
        if (emailField.length > 0) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailField.val())) {
                isValid = false;
                emailField.addClass('is-invalid');
            }
        }
        
        // Phone validation
        const phoneField = form.find('input[name="phone"]');
        if (phoneField.length > 0) {
            const phoneRegex = /^[0-9]{10}$/;
            if (!phoneRegex.test(phoneField.val())) {
                isValid = false;
                phoneField.addClass('is-invalid');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            showAlert('danger', 'Please fill all required fields correctly.');
        }
    });
    
    // =====================================================
    // PASSWORD TOGGLE
    // =====================================================
    $('.toggle-password').on('click', function() {
        const input = $($(this).data('target'));
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // =====================================================
    // IMAGE PREVIEW
    // =====================================================
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // =====================================================
    // COUNTER ANIMATION (Admin Dashboard)
    // =====================================================
    $('.counter').each(function() {
        const $this = $(this);
        const countTo = parseInt($this.text());
        
        $({ countNum: 0 }).animate({
            countNum: countTo
        }, {
            duration: 2000,
            easing: 'linear',
            step: function() {
                $this.text(Math.floor(this.countNum));
            },
            complete: function() {
                $this.text(this.countNum);
            }
        });
    });
    
    // =====================================================
    // SMOOTH SCROLL
    // =====================================================
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });
    
    // =====================================================
    // AUTO HIDE ALERTS
    // =====================================================
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
});

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show alert-custom" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at top of page
    $('body').prepend(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Format price in Indian Rupees
 */
function formatPrice(price) {
    return '₹' + parseFloat(price).toFixed(2);
}

/**
 * Confirm action
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to perform this action?');
}

/**
 * Show loading spinner
 */
function showLoading() {
    const loadingHtml = `
        <div class="loading-overlay">
            <div class="spinner-custom"></div>
        </div>
    `;
    $('body').append(loadingHtml);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    $('.loading-overlay').remove();
}
