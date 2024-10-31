jQuery(document).ready(function ($)
{
    var $loading = $(".showLoading");
    $loading.hide();
    $('#error-message').hide();

    // show payment pending response if this is returned from paypal.
    if (quip_invoices.pending)
    {
        document.body.scrollTop = document.documentElement.scrollTop = 0;
        $('#success-message-text').html(
            quip_invoices.strings.paymentPendingMsg + " " +
            "<a href='" + quip_invoices.pageurl + "' class='btn btn-default btn-xs'>" + quip_invoices.strings.refreshPage + "</a>");
        $('#success-message').show();
    }

    /////////////// Payment ///////////////////
    // new checkout
    var stripe = Stripe(quip_invoices.stripePublicKey);
    var checkoutButton = document.getElementById('payInvoiceButton');
    checkoutButton.addEventListener('click', function() {
        stripe.redirectToCheckout({
            sessionId: checkout
        }).then(function (result) {
            // If `redirectToCheckout` fails due to a browser or network
            // error, display the localized error message to your customer
            // using `result.error.message`.
            // show the errors on the form
            $('#error-message-text').text(result.error.message);
            $('#error-message').show();

        });
    });

});