// FETCH EVERYTHING FROM WAYBILL
function load(data, $container)
{
    $('.icon-th-large').removeClass('icon-spin');
    var waybills = [];

    $.each(data, function (key, value) {
        waybills.push(waybill(value));
    });
    $container.loadData(waybills);
}

function save($container)
{
    $('#btn-save').click(function() {
        $('#msg').show();
        var data = $container.getData();
        if (data.length > 0)
        {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: url + 'waybills/batch_update',
                data: JSON.stringify(data),
                beforeSend: function () {
                    $('#msg').html('Loading ...');
                },
                complete: function (response) {
                    if (response.status == 200)
                    {
                        $('#msg').html('Updated ...').delay(5000).fadeOut(500);

                        $('.icon-th-large').addClass('icon-spin');
                        $.getJSON(search_path(), function (data) {
                            load(data, $container);
                        });
                        // $.getJSON(url + 'waybills', function (data) {
                        //     load(data, $container);
                        // });
                    }
                    else
                    {
                        alert('Internal server error');
                    }
                }
            });
        }
    });
}

// OPEN POPUP MODAL FOR EDITING WAYBILL
function edit_waybill($container)
{
    $('#waybills').on('click', 'a.row-edit', function (e) {
        $('#dialog-msg').removeClass('text-error text-success').html('');
        var $this = $(this);
        var tracking_id = $container.getDataAtRow($(this).data('row')).tracking_id;
        fetch_data_to_modal($container, tracking_id);

        return false;
    });
}

// FETCH DATA TO POPUP MODAL
function fetch_data_to_modal($container, tracking_id)
{        
    $.getJSON(url + 'waybills/' + tracking_id, function (data) {


        // Show modal
        var datum = waybill(data[0]);
        var template = $('#update-modal-template').html();
        var html     = Mustache.to_html(template, datum);
        document.getElementById('modal-append').innerHTML = html;
        $('#update-modal').modal('show');

        $('.date-picker').datepicker().next().on(ace.click_event, function(){
            $(this).prev().focus();
        });

        var couriers = [ '汇通快递', '申通快递', 'EMS', '顺丰快递', '圆通快递', '韵达快递' ];
        $('#update-modal').find('input[name=courier_name]').typeahead({ source: couriers });

        var cities = ['HK', 'SH', 'CQ', 'TJ'];
        $('#update-modal').find('input[name=city_name]').typeahead({ source: cities });

        /********************************** NEED VALIDATION/**********************************/ 
        $('#update-modal').submit(function(ev) {
            var input = $(ev.currentTarget).serializeObject();

            switch (input.courier_name)
            {
                case '汇通快递': input.courier_value = 'huitongkuaidi'; break;
                case 'EMS':      input.courier_value = 'ems';           break;
                case '申通快递': input.courier       = 'shentong';      break;
                case '圆通快递': input.courier_value = 'yuantong';      break;
                case '顺丰快递': input.courier_value = 'shunfeng';      break;
                case '韵达快递': input.courier_value = 'yunda';         break;
                default:         input.courier_value = null;            break;
            }
            update(input, $container);

            return false;
        });

    });
}

// UPDATE WAYBILL
function update(waybill, $container)
{
    $.ajax ({
        url: url + 'waybills/' + waybill.tracking_id,
        dataType: 'json',
        type: 'post',
        data: JSON.stringify(waybill),
        beforeSend: function () {
            $('#dialog-msg').addClass('text-error').html('Loading .....');
        },
        complete: function (res) {
            if (res.status == 200)
            {
                $('#dialog-msg').addClass('text-success').html('Updated .....');
                $('.icon-th-large').addClass('icon-spin');
                // $.getJSON(url + 'waybills', function(data) {
                //     load(data, $container);
                // });
                $.getJSON(search_path(), function (data) {
                    load(data, $container);
                });
            }
            else
                alert('Internal Server Error');
        }
    });
}

function search_path()
{
    var fields = $('#search-form').serializeObject();

    if (fields.courier === '汇通快递')
        fields.courier = 'huitongkuaidi';
    else if (fields.courier === 'EMS')
        fields.courier = 'ems';
    else if (fields.courier === '申通快递')
        fields.courier  = 'shentong';
    else if (fields.courier === '圆通快递')
        fields.courier = 'yuantong';
    else if (fields.courier === '顺丰快递')
        fields.courier = 'shunfeng';
    else if (fields.courier === '韵达快递')
        fields.courier = 'yunda';
    else
        fields.courier = '';
    
    return url + 'waybills?order_date=' + fields.order_date 
                        + '&location=' + fields.location 
                        + '&tracking_id=' + fields.tracking_id 
                        + '&courier=' + fields.courier
                        + '&flight_date=' + fields.flight_date
                        + '&arrival=' + fields.arrival;
}

// SEARCH WAYBILLS
function search($container)
{
    var search_form = $('#search-form');

    var couriers = [ '汇通快递', '申通快递', 'EMS', '顺丰快递', '圆通快递', '韵达快递' ];
    search_form.find('input[name=courier]').typeahead({ source: couriers });

    var cities = ['HK', 'SH', 'CQ', 'TJ'];
    search_form.find('input[name=arrival]').typeahead({ source: cities });

    search_form.submit(function(ev) {
        // Check empty fields
        var empty = $(this).find(':text').filter(function () { return this.value != ""; });
        if (empty.length)
        {
            $.ajax({
                url: search_path(),
                dataType: 'json',
                type: 'get',
                beforeSend: function () {
                    $('.icon-th-large').addClass('icon-spin');
                    search_form.find('.msg').html('Loading ...');
                },
                complete: function (xhr) {
                    $('.icon-th-large').removeClass('icon-spin');
                    var response = JSON.parse(xhr.responseText);
                    switch (xhr.status)
                    {
                        case 200:
                            search_form.find('.msg').html('');
                            load(response, $container);
                        break;
                        case 404: search_form.find('.msg').html(response.error); break;
                        default:  search_form.find('.msg').html('Internal Server Error'); break;
                    }
                }
            });
        }
        return false;
    });
}

function filter($container)
{
    $('#btn-filter').click(function () {
        var checked_cols = [];
        $('.chk-filter').each(function () {
            if (this.checked)
                checked_cols.push($(this).data('col'));
        });
        $container.updateSettings(config(checked_cols));
    });
}

function display_all($container)
{
    $('#btn-display_all').click(function () {
        $container.updateSettings(config([]));
    });
}

function export_waybill($container)
{
    $('#btn-export').click(function () {
        $('#msg').show();
        var data = $container.getData();
        if (data.length > 0)
        {
            $.ajax ({
                url: url + 'waybills/export',
                dataType: 'json',
                type: 'post',
                data: JSON.stringify(data),
                beforeSend: function () {
                    $('#msg').html('Loading ...');
                },
                complete: function (response) {
                    if (response.status == 200)
                    {
                        $('#msg').html('Updated ...').delay(5000).fadeOut(500);
                        var file_url = (url + response.responseText).replace('/index.php', '');
                        window.location.replace(file_url);
                    }
                    else
                    {
                        alert('Internal Server Error');
                    }
                }
            });
        }
    });
}

function visible_data(visible_index)
{
    var visible_colums = [];
    var column_data =  [
        { data: 'tracking_id', readOnly: true },
        { data: 'receiver_name' },
        { data: 'receiver_address' },
        { data: 'receiver_phone' },
        { data: 'receiver_postcode' },
        { data: 'package_total_weight' },
        { data: 'package_description' },
        { data: 'note' },
        { data: 'order_date' },
        { data: 'flight_date' },
        { data: 'city_name', readOnly: true },
        { data: 'location', readOnly: true },
        { data: 'postage' },
        { data: 'insurance' },
        { data: 'tax' },
        { data: 'packing_charge' },
        { data: 'total_price' },
        { data: 'agent_number' },
        { data: 'agent_price' },
        { data: 'courier_name', readOnly: true },
        { data: 'express_number', readOnly: true },
        { data: 'package_quantity' },
        { data: 'package_original_weight' },
        { data: 'package_claim_value' },
        { data: 'package_staff_signature' },
        { data: 'sender_name' },
        { data: 'sender_address' },
        { data: 'sender_city' },
        { data: 'sender_state' },
        { data: 'sender_country' },
        { data: 'sender_postcode' },
        { data: 'sender_phone' },
        { data: 'receiver_city' },
        { data: 'receiver_state' },
        { data: 'receiver_country' }
    ];

    for (var i = 0; i < visible_index.length; i++)
    {
        for (var j = 0; j < column_data.length && visible_colums.length <= visible_index.length; j++)
            if (j == visible_index[i])
                visible_colums.push(column_data[j]);
    }

    return visible_colums.length > 0 ? visible_colums : column_data;
}

function visible_col_header(visible_index)
{
    var visible_header = [];
    var column_header_data = [
        '<input class="chk-filter" type="checkbox" data-col="0"/> <span class="lbl"></span> <span class="col-edit">Tracking Id</span>',
        '<input class="chk-filter" type="checkbox" data-col="1"/> <span class="lbl"></span> <span class="col-edit">Receiver Name</span>',
        '<input class="chk-filter" type="checkbox" data-col="2"/> <span class="lbl"></span> <span class="col-edit">Receiver Address</span>',
        '<input class="chk-filter" type="checkbox" data-col="3"/> <span class="lbl"></span> <span class="col-edit">Receiver Phone</span>',
        '<input class="chk-filter" type="checkbox" data-col="4"/> <span class="lbl"></span> <span class="col-edit">Receiver Postcode</span>',
        '<input class="chk-filter" type="checkbox" data-col="5"/> <span class="lbl"></span> <span class="col-edit">Total Weight</span>',
        '<input class="chk-filter" type="checkbox" data-col="6"/> <span class="lbl"></span> <span class="col-edit">Description</span>',
        '<input class="chk-filter" type="checkbox" data-col="7"/> <span class="lbl"></span> <span class="col-edit">Note</span>',
        '<input class="chk-filter" type="checkbox" data-col="8"/> <span class="lbl"></span> <span class="col-edit">Order Date</span>',
        '<input class="chk-filter" type="checkbox" data-col="9"/> <span class="lbl"></span> <span class="col-edit">Flight Date</span>',
        '<input class="chk-filter" type="checkbox" data-col="10"/> <span class="lbl"></span> <span class="col-edit">Arrival</span>',
        '<input class="chk-filter" type="checkbox" data-col="11"/> <span class="lbl"></span> <span class="col-edit">Location</span>',
        '<input class="chk-filter" type="checkbox" data-col="12"/> <span class="lbl"></span> <span class="col-edit">Postage</span>',
        '<input class="chk-filter" type="checkbox" data-col="13"/> <span class="lbl"></span> <span class="col-edit">Insurance</span>',
        '<input class="chk-filter" type="checkbox" data-col="14"/> <span class="lbl"></span> <span class="col-edit">Tax</span>',
        '<input class="chk-filter" type="checkbox" data-col="15"/> <span class="lbl"></span> <span class="col-edit">Packing Charge</span>',
        '<input class="chk-filter" type="checkbox" data-col="16"/> <span class="lbl"></span> <span class="col-edit">Total Price</span>',
        '<input class="chk-filter" type="checkbox" data-col="17"/> <span class="lbl"></span> <span class="col-edit">Agent No</span>',
        '<input class="chk-filter" type="checkbox" data-col="18"/> <span class="lbl"></span> <span class="col-edit">Agent Price</span>',
        '<input class="chk-filter" type="checkbox" data-col="19"/> <span class="lbl"></span> <span class="col-edit">Express</span>',
        '<input class="chk-filter" type="checkbox" data-col="20"/> <span class="lbl"></span> <span class="col-edit">Express No</span>',
        '<input class="chk-filter" type="checkbox" data-col="21"/> <span class="lbl"></span> <span class="col-edit">Quantity</span>',
        '<input class="chk-filter" type="checkbox" data-col="22"/> <span class="lbl"></span> <span class="col-edit">Original Weight</span>',
        '<input class="chk-filter" type="checkbox" data-col="23"/> <span class="lbl"></span> <span class="col-edit">Claim Value</span>',
        '<input class="chk-filter" type="checkbox" data-col="24"/> <span class="lbl"></span> <span class="col-edit">Staff Signature</span>',
        '<input class="chk-filter" type="checkbox" data-col="25"/> <span class="lbl"></span> <span class="col-edit">Sender Name</span>',
        '<input class="chk-filter" type="checkbox" data-col="26"/> <span class="lbl"></span> <span class="col-edit">Sender Address</span>',
        '<input class="chk-filter" type="checkbox" data-col="27"/> <span class="lbl"></span> <span class="col-edit">Sender City</span>',
        '<input class="chk-filter" type="checkbox" data-col="28"/> <span class="lbl"></span> <span class="col-edit">Sender State</span>',
        '<input class="chk-filter" type="checkbox" data-col="29"/> <span class="lbl"></span> <span class="col-edit">Sender Country</span>',
        '<input class="chk-filter" type="checkbox" data-col="30"/> <span class="lbl"></span> <span class="col-edit">Sender Postcode</span>',
        '<input class="chk-filter" type="checkbox" data-col="31"/> <span class="lbl"></span> <span class="col-edit">Sender Phone</span>',
        '<input class="chk-filter" type="checkbox" data-col="32"/> <span class="lbl"></span> <span class="col-edit">Receiver City</span>',
        '<input class="chk-filter" type="checkbox" data-col="33"/> <span class="lbl"></span> <span class="col-edit">Receiver State</span>',
        '<input class="chk-filter" type="checkbox" data-col="34"/> <span class="lbl"></span> <span class="col-edit">Receiver Country</span>',
    ];

    for (var i = 0; i < visible_index.length; i++)
    {
        for (var j = 0; j < column_header_data.length && visible_header.length <= visible_index.length; j++)
            if (j == visible_index[i])
                visible_header.push(column_header_data[j]);
    }

    return visible_header.length > 0 ? visible_header : column_header_data;
}

// config handsontable
function config(checked_cols)
{
    return {
        width: 10000,
        startRows: 0,
        rowHeaders: function (row) {
            return '<a href="#" class="row-edit" data-row=' + row + ' >Edit</a>';
        },
        colHeaders: visible_col_header(checked_cols),
        columns: visible_data(checked_cols),
        manualColumnResize: true,
   };
}

// GET HANDSONTABLE INSTANCE
function container()
{
    $('#waybills').handsontable(config([]));
    return $('#waybills').handsontable('getInstance');
}

// Main
$(function(){
    var $container = container();
    $('#waybills table').addClass('table table-hover');

    // $.getJSON(url + 'waybills', function(data) {
    //     load(data, $container);
    // });

    // $('#waybills').on('mousedown', 'th:has(.colHeader)', function () {
    // });

    edit_waybill($container);
    search($container);
    export_waybill($container);
    save($container);
    filter($container);
    display_all($container);
});
