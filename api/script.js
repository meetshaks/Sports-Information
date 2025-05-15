$(document).ready(function() {
    // Initialize DataTables with responsive configuration and disabled features
    $('.data-table').DataTable({
        responsive: false, // Disable responsive collapsing entirely
        paging: false,
        lengthChange: false,
        searching: false,
        language: {
            info: "" // Removes "Showing X to Y of Z entries"
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

    // Back to Top button
    $(window).scroll(function() {
        if ($(this).scrollTop() > 200) {
            $('#backToTop').removeClass('d-none');
        } else {
            $('#backToTop').addClass('d-none');
        }
    });

    $('#backToTop').click(function() {
        $('html, body').animate({ scrollTop: 0 }, 500);
        return false;
    });

    // Inactivity logout timer
    let inactivityTimeout;
    const inactivityLimit = 120000; // 2 minutes in milliseconds

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
                window.location.href = 'logout.php';
            });
        }, inactivityLimit);
    }

    // Reset timer on user activity
    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'].forEach(function(event) {
        document.addEventListener(event, resetInactivityTimer);
    });

    // Start the timer on page load
    resetInactivityTimer();
});