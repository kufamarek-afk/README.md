(function ($) {
    function handleAvailability(form) {
        var $checkIn = form.find('#cpk-check-in');
        var $checkOut = form.find('#cpk-check-out');
        var $message = $('.cpk-availability-message');

        function updateMessage(text, type) {
            if (!$message.length) {
                return;
            }

            if (!text) {
                $message.text('').removeClass('is-success is-error');
                return;
            }

            $message.text(text)
                .removeClass('is-success is-error')
                .addClass(type);
        }

        function checkAvailability() {
            var checkInVal = $checkIn.val();
            var checkOutVal = $checkOut.val();

            if (!checkInVal || !checkOutVal) {
                updateMessage('', '');
                return;
            }

            updateMessage(window.cpkBooking ? window.cpkBooking.loadingText || 'Overujeme dostupnosť…' : 'Overujeme dostupnosť…', '');

            $.ajax({
                url: window.cpkBooking.restUrl,
                method: 'POST',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', window.cpkBooking.nonce);
                },
                data: {
                    check_in: checkInVal,
                    check_out: checkOutVal
                }
            }).done(function (response) {
                if (response.available) {
                    updateMessage(response.message || window.cpkBooking.successText, 'is-success');
                } else {
                    updateMessage(response.message || window.cpkBooking.errorText, 'is-error');
                }
            }).fail(function (xhr) {
                var message = window.cpkBooking.errorText;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                updateMessage(message, 'is-error');
            });
        }

        $checkIn.on('change', checkAvailability);
        $checkOut.on('change', checkAvailability);
    }

    function enhanceAlerts(form) {
        var $success = form.find('.cpk-alert--success');
        var $error = form.find('.cpk-alert--error');

        form.on('submit', function () {
            $success.attr('hidden', true);
            $error.attr('hidden', true);
        });

        var params = new URLSearchParams(window.location.search);
        if (params.has('cpk_booking_success')) {
            $success.text(window.cpkBooking.successText).attr('hidden', false);
        }
        if (params.has('cpk_booking_error')) {
            $error.text(window.cpkBooking.errorText).attr('hidden', false);
        }
    }

    $(function () {
        var $form = $('.cpk-booking-form');
        if ($form.length) {
            handleAvailability($form);
            enhanceAlerts($form);
        }
    });
})(jQuery);
