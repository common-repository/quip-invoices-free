jQuery(document).ready(function ($)
{
    var $loading = $(".showLoading");
    $loading.hide();

    ////////////////// Helpers ///////////////////////

    function fsa_do_ajax_post(ajaxurl, form, successMessage, doRedirect)
    {
        $loading.show();
        // Disable the submit button
        form.find('button').prop('disabled', true);

        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: form.serialize(),
            cache: false,
            dataType: "json",
            success: function (data)
            {
                $loading.hide();
                document.body.scrollTop = document.documentElement.scrollTop = 0;

                if (data.success)
                {
                    fsa_showUpdate(successMessage);
                    form.find('button').prop('disabled', false);
                    fsa_resetForm(form);

                    if (doRedirect)
                    {
                        setTimeout(function ()
                        {
                            window.location = data.redirectURL;
                        }, 1000);
                    }
                }
                else
                {
                    // re-enable the submit button
                    form.find('button').prop('disabled', false);
                    // show the errors on the form
                    fsa_showError(data.msg);
                }
            }
        });
    }

    ////////////////////////////

    function calc_line_item_amount()
    {
        var amount = 0.0;
        var rate = $("#liRate").val();
        var qty = $("#liQty").val();
        var adj = $("#liAdj").val();

        if ($.isNumeric(rate) && $.isNumeric(qty) && $.isNumeric(adj))
        {
            var sub = parseFloat(rate) * parseFloat(qty);
            var percent = (100 + parseFloat(adj)) / 100;
            amount = sub * percent;
        }

        return amount.toFixed(2);
    }

    function calc_line_item_subtotal()
    {
        var subtotal = 0.0;
        // add up the existing line item amounts
        $("#lineItemsTable > tbody > tr").each(function (i, row)
        {
            var $row = $(row);
            var attr = $row.attr('data-amount');

            if (typeof attr !== typeof undefined && attr !== false)
            {
                subtotal += parseFloat(attr);
            }
        });

        // add the currently editing line item, if any
        var editingAmount = unformat_amount($("#liAmount").text());
        if ($.isNumeric(editingAmount))
            subtotal += parseFloat(editingAmount);

        return subtotal.toFixed(2);
    }

    function calc_line_item_total(subtotal)
    {
        var total = parseFloat(subtotal);
        var tax = $("#invoiceTaxRate").val();
        if ($.isNumeric(tax))
        {
            var percent = (100 + parseFloat(tax)) / 100;
            total = subtotal * percent;
        }

        return total.toFixed(2);
    }

    function format_amount(amount)
    {
        return numeral(amount).format('0,0.00');
    }

    function unformat_amount(amount)
    {
        return numeral().unformat(amount);
    }

    //automatic line item amount calculation
    $("#liRate, #liQty, #liAdj, #invoiceTaxRate").keyup(function ()
    {
        $("#liAmount").text(quip_invoices.symbol + format_amount(calc_line_item_amount()));
        // totals
        var subtotal = calc_line_item_subtotal();
        var total = calc_line_item_total(subtotal);
        $("#liSubTotal").text(quip_invoices.symbol + format_amount(subtotal));
        $("#liTotal").text(quip_invoices.symbol + format_amount(total));
    }).keyup();

    $("#addLineItemButton").click(function (e)
    {
        e.preventDefault();

        fsa_clearUpdateAndError();
        if (fsa_validField($("#liTitle"), quip_invoices.strings.title) && fsa_validField($("#liRate"), quip_invoices.strings.rate) &&
            fsa_validField($("#liQty"), quip_invoices.strings.quantity) && fsa_validField($("#liAdj"), quip_invoices.strings.adj))
        {
            var title = $("#liTitle").val();
            var rate = $("#liRate").val();
            var qty = $("#liQty").val();
            var adj = $("#liAdj").val();

            if ($.isNumeric(rate) && $.isNumeric(qty) && $.isNumeric(adj))
            {
                var amount = calc_line_item_amount();
                var item = {"title": title, "rate": rate, "quantity": qty, "adjustment": adj, "total": amount};
                var itemJSON = JSON.stringify(item);

                var row = "<tr data-json='" + Base64.encode(itemJSON) + "' data-amount='" + amount + "'>";
                row += "<td>" + title + "</td>";
                row += "<td>" + rate + "</td>";
                row += "<td>" + qty + "</td>";
                row += "<td>" + adj + "</td>";
                row += "<td>" + quip_invoices.symbol + format_amount(amount) + "</td>";
                row += "<td><a class='button deleteLineItemButton' href='delete'>" + quip_invoices.strings.delete + "</a></td>";
                row += "</tr>";
                $('#lineItemsTable').find('tr:last').before(row);
                //clear
                $("#liTitle").val("");
                $("#liRate").val(0);
                $("#liQty").val(1);
                $("#liAdj").val(0);
            }
            else
            {
                fsa_showError(quip_invoices.strings.invoiceMsgLineItemNumeric)
            }
        }

        return false;
    });


    $("#lineItemsTable").on('click', '.deleteLineItemButton', function (e)
    {
        e.preventDefault();

        var row = $(this).parents('tr:first');
        $(row).hide('slow', function ()
        {
            $(row).remove();
            //force recalc
            $("#liRate").keyup();
        });

        return false;
    });


    $('#quip-invoices-edit-template-form').submit(function ()
    {
        fsa_clearUpdateAndError();
        if (fsa_validField($("#name"), "Template Name"))
        {
            // add line items to the form
            var lineItems = [];
            $("#lineItemsTable > tbody > tr").each(function (i, row)
            {
                var $row = $(row);
                var attr = $row.attr('data-json');

                if (typeof attr !== typeof undefined && attr !== false)
                {
                    lineItems.push(Base64.decode(attr));
                }
            });

            var $form = $(this);

            if (lineItems.length > 0)
            {
                $form.append("<input type='hidden' name='lineItems' id='lineItems' value='" + Base64.encode(JSON.stringify(lineItems)) + "' />");
            }
            else
            {
                fsa_showError(quip_invoices.strings.invoiceMsgLineItemMissing);
                return false;
            }

            //post form via ajax
            fsa_do_ajax_post(quip_invoices.ajaxurl, $form, "Template updated successfully.", true);
        }

        return false;
    });


    // company details update/change/cancel
    $('#companyDetailsChange').click(function (e)
    {
        e.preventDefault();

        $(this).hide();
        $('#companyDetailsDisplay').hide();
        $('#companyDetails').show();
        $('#companyDetailsChangeSave').show();
        $('#companyDetailsChangeCancel').show();

        return false;
    });

    $('#companyDetailsChangeCancel').click(function (e)
    {
        e.preventDefault();

        $('#companyDetailsChange').show();
        $('#companyDetailsDisplay').show();
        $(this).hide();
        $('#companyDetails').hide();
        $('#companyDetailsChangeSave').hide();

        return false;
    });

    $('#companyDetailsChangeSave').click(function (e)
    {
        e.preventDefault();

        var newDetails = nl2br($('#companyDetails').val());
        $('#companyDetailsDisplay').html(newDetails);
        //lazy hide
        $('#companyDetailsChangeCancel').click();

        return false;
    });

    $('#paymentTypeMail, #paymentTypePhone').click(function ()
    {
        if ($('#paymentTypeMail').is(':checked') || $('#paymentTypePhone').is(':checked'))
        {
            $('#paymentInstructionsSection').show('fast');
        }
        else if (!$('#paymentTypeMail').is(':checked') && !$('#paymentTypePhone').is(':checked'))
        {
            $('#paymentInstructionsSection').hide('fast');
        }
    });


    function delete_invoice()
    {
        $loading.show();
        var id = $(document).data('invoiceID');

        $.ajax({
            type: "POST",
            url: quip_invoices.ajaxurl,
            data: {"action": "quip_invoices_delete_invoice", "id": id},
            cache: false,
            dataType: "json",
            success: function (data)
            {
                $("#deleteInvoiceDialog").dialog("close");
                $loading.hide();
                fsa_showUpdate(quip_invoices.strings.invoiceMsgDeleted);

                setTimeout(function ()
                {
                    window.location.reload(true);
                }, 1000);
            }
        });
    }

    $("#deleteInvoiceDialog").dialog({
        autoOpen: false,
        height: 200,
        width: 350,
        modal: true,
        buttons: [
            {
                text: quip_invoices.strings.yes,
                click: delete_invoice
            },
            {
                text: quip_invoices.strings.no,
                click: function ()
                {
                    $(this).dialog("close")
                }
            }
        ]
    });

    $('.delete-invoice').click(function (e)
    {
        e.preventDefault();
        var id = $(this).attr('data-id');
        $(document).data('invoiceID', id);
        $("#deleteInvoiceDialog").dialog("open");
        return false;
    });





});