jQuery(document).ready(function ($)
{
    var $loading = $(".showLoading");
    $loading.hide();

    // setup datepickers and default create to today.
    $("#invoiceCreateDate").datepicker({
        dateFormat: "DD, d MM, yy",
        altField: "#invoiceCreateDateDB",
        altFormat: "dd-mm-yy"
    });

    if (!quip_invoices.edit)
        $("#invoiceCreateDate").datepicker('setDate', new Date());

    $("#invoiceDueDate").datepicker({
        dateFormat: "DD, d MM, yy",
        altField: "#invoiceDueDateDB",
        altFormat: "dd-mm-yy"
    });

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


    $('#quip-invoices-create-invoice-form').submit(function ()
    {
        fsa_clearUpdateAndError();
        if (fsa_validField($("#invoiceNumber"), quip_invoices.strings.invoiceNumber) &&
            fsa_validField($("#invoiceCreateDate"), quip_invoices.strings.invoiceDate) &&
            fsa_validField($("#invoiceDueDate"), quip_invoices.strings.dueDate))
        {
            //check we have a client selected
            var clientID = $('#invoiceClient').val();
            if (clientID == null || typeof clientID == typeof undefined || clientID == "")
            {
                fsa_showError(quip_invoices.strings.invoiceMsgClientSelected);
                return false;
            }

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
            fsa_do_ajax_post(quip_invoices.ajaxurl, $form, quip_invoices.strings.invoiceMsgSuccess, true);
        }

        return false;
    });

    // show the recurring frequency section
    $('#invoiceRepeat').click(function()
    {
       $('#invoiceRepeatSection').toggle("fast");
    });


    $('#createNewClient').click(function (e)
    {
        $("#createClientDialog").dialog("open");
        return false;
    });


    function create_client()
    {
        fsa_clearUpdateAndError();

        if (fsa_validField($('#clientName'), quip_invoices.strings.clientName) && fsa_validField($('#clientEmail'), quip_invoices.strings.clientEmail))
        {
            $loading.show();
            $(this).prop('disabled', true);

            var name = $('#clientName').val();
            var email = $('#clientEmail').val();

            $.ajax({
                type: "POST",
                url: quip_invoices.ajaxurl,
                data: {action: "quip_invoices_quick_create_client", name: name, email: email},
                cache: false,
                dataType: "json",
                success: function (data)
                {
                    $loading.hide();
                    $('#createClientButton').prop('disabled', false);

                    if (data.success)
                    {
                        $('#invoiceClientText').val(name + ' (' + email + ')');
                        $('#invoiceClient').val(data.id);
                        $('#clientName').val("");
                        $('#clientEmail').val("");
                        $("#createClientDialog").dialog("close");
                    }
                    else
                    {
                        fsa_showError(data.msg);
                        $('#clientEmail').focus();
                    }
                }
            });
        }

        return false;
    }


    $("#createClientDialog").dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: [
            {
                text: quip_invoices.strings.add,
                click: create_client
            },
            {
                text: quip_invoices.strings.cancel,
                click: function ()
                {
                    $(this).dialog("close")
                }
            }
        ]
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
    // and check on load
    if ($('#paymentTypeMail').is(':checked') || $('#paymentTypePhone').is(':checked'))
    {
        $('#paymentInstructionsSection').show();
    }


    //for uploading files using WordPress media library
    var custom_uploader;
    function uploadFile(inputID)
    {
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader)
        {
            custom_uploader.open();
            return;
        }

        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title:'Choose File',
            button:{
                text:'Choose File'
            },
            multiple:false
        });

        //When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on('select', function ()
        {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            $(inputID).val(attachment.url);
        });

        //Open the uploader dialog
        custom_uploader.open();
    }

    //upload attachment
    $('#uploadAttachmentButton').click(function (e)
    {
        e.preventDefault();
        uploadFile('#attachment');
    });

    $('#quip-invoices-send-invoice-form').submit(function ()
    {
        //force the tinymce instance to save the content - in case user makes changes without clicking anything else.
        tinymce.triggerSave();

        fsa_clearUpdateAndError();
        if (!fsa_validField($('#toAddress'), quip_invoices.strings.toAddress) ||
            !fsa_validField($('#subject'), quip_invoices.strings.emailSubject) ||
            !fsa_validField($('#message'), quip_invoices.strings.emailMessage))
        {
            return false;
        }

        var $form = $(this);
        //post form via ajax
        fsa_do_ajax_post(quip_invoices.ajaxurl, $form, quip_invoices.strings.invoiceMsgSent, true);
        return false;
    });

    // copy an invoice from the invoices page
    $('.copy-invoice').click(function (e)
    {
        e.preventDefault();
        $loading.show();

        var id = $(this).attr('data-id');

        $.ajax({
            type: "POST",
            url: quip_invoices.ajaxurl,
            data: {"action": "quip_invoices_copy_invoice", "id": id},
            cache: false,
            dataType: "json",
            success: function (data)
            {
                $loading.hide();
                fsa_showUpdate(quip_invoices.strings.invoiceMsgCopied);

                setTimeout(function ()
                {
                    window.location = data.redirectURL;
                }, 1000);
            }
        });

        return false;
    });

    // generate a pdf from the invoices page
    $('.pdf-invoice').click(function (e)
    {
        //SIMON V2.0
        e.preventDefault();
        $loading.show();
        $("#upgradePDFDialog").dialog({
            resizable: false,
            height: "auto",
            width: 400,
            height: 200,
            modal: true
        });
        return false;
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

    function delete_template()
    {
        $loading.show();
        var id = $(document).data('templateID');

        $.ajax({
            type: "POST",
            url: quip_invoices.ajaxurl,
            data: {"action": "quip_invoices_delete_template", "id": id},
            cache: false,
            dataType: "json",
            success: function (data)
            {
                $("#deleteTemplateDialog").dialog("close");
                $loading.hide();
                fsa_showUpdate("Template deleted successfully.");

                setTimeout(function ()
                {
                    window.location.reload(true);
                }, 1000);
            }
        });
    }

    $("#deleteTemplateDialog").dialog({
        autoOpen: false,
        height: 200,
        width: 350,
        modal: true,
        buttons: [
            {
                text: quip_invoices.strings.yes,
                click: delete_template
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

    $('.delete-template').click(function (e)
    {
        e.preventDefault();
        var id = $(this).attr('data-id');
        $(document).data('templateID', id);
        $("#deleteTemplateDialog").dialog("open");
        return false;
    });


    // bulk actions
    $('#bulk-action-form').submit(function(e)
    {
        e.preventDefault();
        fsa_clearUpdateAndError();
        var type = $(this).find('input[name="type"]').val();
        var action = $('select[name=action]').val();
        var action2 = $('select[name=action2]').val();
        if (action == -1 && action2 == -1)
        {
            fsa_showError('You must select an action to apply.');
            return false;
        }

        // grab all the rows selected
        var selectedIDs = $('#bulk-action-form').find('input:checked').map(function(){
             return $.isNumeric($(this).val()) ? $(this).val() : 0;
        });

        // construct the backend action from the type and selected bulk action
        var ajax_action = "quip_invoices_";

        if (action === 'delete' || action2 === 'delete')
        {
            ajax_action += "delete_" + type;
            $loading.show();
            $('input[type="submit"]').prop('disabled', true);

            // delete all in checked[]
            $.ajax({
                type: "POST",
                url: quip_invoices.ajaxurl,
                data: {"action": ajax_action, "ids": selectedIDs.get()},
                cache: false,
                dataType: "json",
                success: function (data)
                {
                    $loading.hide();
                    fsa_showUpdate(data.msg);

                    setTimeout(function ()
                    {
                        window.location.reload(true);
                    }, 1000);
                }
            });
        }

        return false;
    });

    // searching clients
    $( "#invoiceClientText" ).autocomplete({
        delay: 500,
        minLength: 3,
        source: function( request, response ) {
            $.ajax({
                type: "POST",
                url: quip_invoices.ajaxurl,
                dataType: "json",
                data: {"action": "quip_invoices_search_clients", "term": request.term},
                success: function( data ) {
                    response( $.map( data, function( item ) {
                        return {
                            label: item.clientName + ' (' + item.clientEmail + ')',
                            value: item.id
                        }
                    }));
                }
            });
        },
        focus: function( event, ui ) {
            event.preventDefault();
            return false;
        },
        select: function( event, ui ) {
            $( "#invoiceClientText" ).val( ui.item.label );
            $( "#invoiceClient" ).val( ui.item.value );
            return false;
        }
    });

});