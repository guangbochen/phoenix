// FETCH EVERYTHING FROM WAYBILL
function load(data, $container)
{
    $('.icon-cloud-upload').removeClass('icon-spin');
    var waybills = [];

    $.each(data, function (key, value) {
        waybills.push(waybill(value));
    });
    $container.loadData(waybills);
}

// SAVE
function save($container)
{
    $('#btn-save').click(function() {
        var data = $container.getData();
        if (data.length > 0)
        {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: url + 'waybills/batch_update',
                data: JSON.stringify(data),
                complete: function (response) {
                    if (response.status == 200)
                    {
                        $('.icon-cloud-upload').addClass('icon-spin');
                        // $.getJSON(url + 'waybills', function (data) {
                        //     load(data, $container);
                        // });
                        $.getJSON(search_path(), function (data) {
                            load(data, $container);
                        });
                        alert('updated');
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

function change($container)
{
    var changes = [];

    $container.addHook('beforeAutofill', function (start, end, data) {
        changes = [];
        var start_row = start.row;
        var end_row   = end.row;
        var column    = start.col
        var length    = data.length;
        var sequence  = parseInt(data[length - 1][0]);

        var range;
        if ( data.length > 1 )
        {
            range = data[length - 1][0] - data[length - 2][0];
            if (range == 0) 
                range = data[data.length - 2][0];
        }
        else
        {
            range = parseInt(data[0][0]);
        }

        for ( var i = start_row; i <= end_row; i++ )
        {
            sequence += range;
            changes.push([i, column, sequence]);
        }
    });

    $container.addHook('afterChange', function (c, source) {
        if (source == 'loadData') return;

        if (source == 'edit')
        {
            console.log('change edit');
        }
        else if (source == 'autofill')
        {
            $container.setDataAtCell(changes);
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
                    $('.icon-cloud-upload').addClass('icon-spin');
                    search_form.find('.msg').html('Loading ...');
                },
                complete: function (xhr) {
                    $('.icon-cloud-upload').removeClass('icon-spin');
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

function getColAtProb(prob)
{
    var probs = ['tracking_id', 'order_date', 'location', 'courier_name', 'express_number'];
    return probs.indexOf(prob);
}

function update($container, $modal, $btn)
{
    var checked_waybills = [];
    var msg = $modal.find('.msg');

    $btn.click(function(){
        // Reset stuff
        msg.empty();
        checked_waybills = [];
        $modal.find('.control-group').removeClass('error');
        $modal.trigger('reset');

        // Collect checked checkbox
        $('.chk-tracking-id').each(function(){
            if (this.checked)
            {
                var checked_waybill = $container.getDataAtRow($(this).data('row'));
                checked_waybills.push(checked_waybill);
            }
        });
    });

    $modal.submit(function(ev){
        msg.empty();
        msg.removeClass('text-sucess text-warning orange');

        var $this = $(this);
        var input  = $(ev.currentTarget).serializeObject();

        if (input.courier_value === '' || input.city_name === '')
        {
            $this.find('.control-group').addClass('error');
            msg.addClass('text-warning orange')
               .append('<i class="icon-warning-sign"></i> Please choose one option');
        }
        else
        {
            $this.find('.control-group').removeClass('error');
            // Add tracking id list to data then send them all to server
            if (checked_waybills.length > 0)
            {
                $.each (checked_waybills, function (i, checked_waybill) {
                    checked_waybill.city_name     = input.city_name || checked_waybill.city_name;
                    checked_waybill.courier_value = input.courier_value || checked_waybill.courier_value;
                });
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: url + 'waybills/batch_update',
                    data: JSON.stringify(checked_waybills),
                    beforeSend: function () {
                        msg.addClass('orange').html('Loading ...');
                    },
                    complete: function (response) {
                        if (response.status == 200)
                        {
                            $this.find('.control-group').removeClass('error');
                            msg.removeClass('tex-warning orange').html('');
                            msg.addClass('text-success')
                               .append('<i class="icon-ok green"></i> Updated successfully');
                            $.getJSON(search_path(), function (data) {
                                load(data, $container);
                            });
                            // $.getJSON(url + 'waybills', function (data) {
                            //     load(data, $container);
                            // });
                        }
                        else
                        {
                            msg.addClass('text-warning orange')
                               .append('<i class="icon-warning-sign"></i> Sorry ! Internal server error');
                        }
                    }
                });
            }
            else
            {
                msg.addClass('text-warning orange')
                   .append("<i class='icon-warning-sign'></i> You haven't checked any waybills");
            }
        }
        return false;
    }); 
}

function add_status($container) {

    var waybill_ids = [];
    var msg = $('#add-status-modal').find('.msg');

    $('#btn-add-status').click(function(){
        // Reset stuff
        msg.empty();
        waybill_ids = [];
        $('#add-status-modal').trigger('reset');
        $('#add-status-modal').find('.control-group').removeClass('error');

        // Collect checked checkbox
        $('.chk-tracking-id').each(function(){
            if (this.checked)
            {
                var waybill_id = $container.getDataAtRow($(this).data('row')).id;
                waybill_ids.push(waybill_id);
            }
        });
    });

    $('#add-status-modal').submit(function(ev){
        msg.empty();
        msg.removeClass('text-sucess text-warning orange');

        var $this = $(this);
        var data  = $(ev.currentTarget).serializeObject();

        if (data.delivery_time === '' || data.delivery_context === '')
        {
            $this.find('.control-group').addClass('error');
            msg.addClass('text-warning orange')
               .append('<i class="icon-warning-sign"></i> Please enter the required fields');
        }
        else
        {
            // Add tracking id list to data then send them all to server
            if (waybill_ids.length > 0)
            {
                data.waybill_ids = waybill_ids;
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: url + 'statuses',
                    data: JSON.stringify(data),
                    complete: function (response) {
                        if (response.status == 200)
                        {
                            $this.find('.control-group').removeClass('error');
                            msg.addClass('text-success')
                               .append('<i class="icon-ok green"></i> Status is added successfully');
                            $.getJSON(search_path(), function (data) {
                                load(data, $container);
                            });
                        }
                        else
                        {
                            $this.find('.control-group').addClass('error');
                            msg.addClass('text-warning orange')
                               .append('<i class="icon-warning-sign"></i> Sorry ! Internal server error');
                        }
                    }
                });
            }
            else
            {
                msg.addClass('text-warning orange')
                   .append("<i class='icon-warning-sign'></i> You haven't checked any waybills");
            }
        }
        return false;
    }); 
}

// GET HANDSONTABLE INSTANCE
function container()
{
    $('#waybills').handsontable(config());
    return $('#waybills').handsontable('getInstance');
}

// config handsontable
function config()
{
   return {
        startRows: 0,
        rowHeaders: function (row) {
            return '<input type="checkbox" class="chk-tracking-id" data-row="' + row + '"/> <span class="lbl"></span>';
        },
        colHeaders: function(col) {
            switch (col)
            {
                case 0: return '<span class="col-edit">Tracking Id</span>';
                case 1: return '<span class="col-edit">Order Date</span>';
                case 2: return '<span class="col-edit">Flight Date</span>';
                case 3: return '<span class="col-edit">Location</span>';
                case 4: return '<span class="col-edit">Arrival</span>';
                case 5: return '<span class="col-edit">Express Company</span>';
                case 6: return '<span class="col-edit">Express Number</span>';
            }
        },
        columns: [
            { data: 'tracking_id', readOnly: true },
            { data: 'order_date', readOnly: true },
            { data: 'flight_date', readOnly: true },
            { data: 'location', readOnly: true },
            { data: 'city_name', readOnly: true },
            { data: 'courier_name', readOnly: true },
            { data: 'express_number' },
        ],
        manualColumnResize: true,
   };
}

// Main
$(function(){
    var $container = container();
    $('#waybills table').addClass('table table-hover');
    change($container);

    // $.getJSON(url + 'waybills', function(data) {
    //     load(data, $container);
    // });
    
    search($container);
    add_status($container);
    update($container, $('#update-express-modal'), $('#btn-update-express'));
    update($container, $('#update-arrival-modal'), $('#btn-update-arrival'));
    // update_express($container);
    // update_arrival($container);
    save($container);
});
