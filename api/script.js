$(document).ready(function() {
    // Initialize DataTables with responsive configuration and disabled features
    $('.data-table').DataTable({
        responsive: false,
        paging: false,
        lengthChange: false,
        searching: false,
        language: {
            info: ""
        }
    });

    // Debug swipe events on mobile
    $('.table-responsive').on('touchstart touchmove touchend', function(e) {
        console.log('Swipe event detected:', e.type, 'Scroll Left:', $(this).scrollLeft());
    });

    // Form validation
    $('#scheduleForm, #editForm').on('submit', function(e) {
        let tourName = $(this).find('input[name="tour_name"]').val().trim();
        let bookingPrice = $(this).find('input[name="booking_price"]').val();
        let sellEarnPrice = $(this).find('input[name="sell_earn_price"]').val();

        if (tourName.length < 2) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Tour name must be at least 2 characters long.',
                customClass: {
                    popup: 'swal2-mobile'
                }
            });
            return;
        }

        if (bookingPrice && bookingPrice < 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Booking price cannot be negative.',
                customClass: {
                    popup: 'swal2-mobile'
                }
            });
            return;
        }

        if (sellEarnPrice && sellEarnPrice < 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Invalid Input',
                text: 'Sell/Earn price cannot be negative.',
                customClass: {
                    popup: 'swal2-mobile'
                }
            });
            return;
        }
    });

    // Delete button handler (only for overview.php)
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        console.log('Delete button clicked for ID:', id, 'Redirecting to: delete.php?id=' + id);
        Swal.fire({
            title: 'Are you sure?',
            text: "This schedule will be permanently deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            customClass: {
                popup: 'swal2-mobile'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                try {
                    window.location.href = `delete.php?id=${id}`;
                } catch (e) {
                    console.error('Error redirecting to delete.php:', e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to initiate deletion.',
                        customClass: {
                            popup: 'swal2-mobile'
                        }
                    });
                }
            }
        }).catch((error) => {
            console.error('SweetAlert2 error:', error);
        });
    });

    // Custom Scrollbar functionality
    function updateScrollProgress() {
        const windowHeight = $(window).height();
        const documentHeight = $(document).height();
        const scrollTop = $(window).scrollTop();
        const scrollableHeight = documentHeight - windowHeight;
        const scrollbarHeight = $('.custom-scrollbar').height();
        const thumbHeight = Math.max(50, (windowHeight / documentHeight) * scrollbarHeight);
        const maxTop = scrollbarHeight - thumbHeight;
        const scrollPercentage = scrollableHeight > 0 ? (scrollTop / scrollableHeight) : 0;
        const thumbPosition = scrollPercentage * maxTop;

        $('.scrollbar-progress').css({
            'height': `${thumbHeight}px`,
            'top': `${thumbPosition}px`
        });

        if (scrollTop > 200) {
            $('#backToTop').removeClass('d-none');
            $('.custom-scrollbar').css('opacity', '1');
        } else {
            $('#backToTop').addClass('d-none');
            $('.custom-scrollbar').css('opacity', '0.5');
        }
    }

    // Draggable scrollbar functionality
    let isDragging = false;
    let startY, startTop;

    $('.scrollbar-progress').on('mousedown touchstart', function(e) {
        isDragging = true;
        $('.scrollbar-progress').addClass('dragging');
        startY = (e.type === 'touchstart' ? e.originalEvent.touches[0].pageY : e.pageY);
        startTop = parseFloat($(this).css('top')) || 0;
        e.preventDefault();
    });

    function updateScrollPosition(pageY) {
        if (!isDragging) return;
        const deltaY = pageY - startY;
        const scrollbarHeight = $('.custom-scrollbar').height();
        const thumbHeight = $('.scrollbar-progress').height();
        const maxTop = scrollbarHeight - thumbHeight;
        let newTop = startTop + deltaY;

        // Clamp the thumb position
        newTop = Math.max(0, Math.min(newTop, maxTop));
        $('.scrollbar-progress').css('top', newTop);

        // Map thumb position to scroll position
        const scrollPercentage = newTop / maxTop;
        const documentHeight = $(document).height() - $(window).height();
        const targetScroll = scrollPercentage * documentHeight;

        window.scrollTo({ top: targetScroll, behavior: 'auto' });
    }

    $(document).on('mousemove touchmove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        const pageY = (e.type === 'touchmove' ? e.originalEvent.touches[0].pageY : e.pageY);
        requestAnimationFrame(() => updateScrollPosition(pageY));
    });

    $(document).on('mouseup touchend', function() {
        if (isDragging) {
            isDragging = false;
            $('.scrollbar-progress').removeClass('dragging');
        }
    });

    // Update scrollbar on scroll
    $(window).on('scroll', function() {
        if (!isDragging) {
            updateScrollProgress();
        }
    });

    // Initialize scrollbar
    updateScrollProgress();

    // Smooth scrolling for back-to-top button and FAB top item
    $('#backToTop, .fab-item.top').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({ scrollTop: 0 }, 500);
    });

    // Smooth scrolling when clicking the scrollbar track
    $('.custom-scrollbar').on('click', function(e) {
        if ($(e.target).hasClass('scrollbar-progress')) return;
        const scrollbarHeight = $(this).height();
        const thumbHeight = $('.scrollbar-progress').height();
        const clickY = e.pageY - $(this).offset().top;
        const maxTop = scrollbarHeight - thumbHeight;
        const targetTop = Math.max(0, Math.min(clickY - thumbHeight / 2, maxTop));
        const scrollPercentage = targetTop / maxTop;
        const documentHeight = $(document).height() - $(window).height();
        const targetScroll = scrollPercentage * documentHeight;

        $('html, body').animate({ scrollTop: targetScroll }, 500);
    });

    // Floating Action Button toggle
    $('.fab-main').click(function() {
        $('.fab-menu').toggleClass('active');
        $(this).find('i').toggleClass('bi-plus-lg bi-x-lg');
    });

    // Inactivity logout timer (skip for viewer.php)
    if (window.location.pathname.indexOf('viewer.php') === -1) {
        let inactivityTimeout;
        const inactivityLimit = 600000;

        function resetInactivityTimer() {
            clearTimeout(inactivityTimeout);
            inactivityTimeout = setTimeout(function() {
                Swal.fire({
                    icon: 'info',
                    title: 'Session Expired',
                    text: 'You have been logged out due to inactivity.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'logout.php?from=other';
                });
            }, inactivityLimit);
        }

        ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, resetInactivityTimer);
        });

        resetInactivityTimer();
    }
});