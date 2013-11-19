$(function() {
    // $.getJSON(url + 'waybills', function(data) {
    //     load(data, false);
    // });
    search();
});

function load(data, is_search)
{
    // FETCH EVERYTHING FROM WAYBILL
    $('.icon-eye-open').removeClass('icon-spin');
    var waybills = [];
    $.each(data, function(key, val) {
        waybills.push(waybill(val));
    });

    var source = $('#waybill-template').html();
    var template = Handlebars.compile(source);
    if (is_search)
    {
        $('#waybills').find('tr').remove();
    }
    $('#waybills').append(template(waybills));

    //call functions once data loaded
    add_waybill_flag();
    check_status(waybills);
    inline_editing(waybills);
    update_waybill_status(waybills);
    // batch_check_status(waybills);
}

/************************ GET postal api Status check_status.html *************************************/
//this function check the waybill status using postal api
function check_status(waybills)
{
    var apiurl = 'http://www.kuaidi100.com/api';
    var AppKey = '63a8b4c04d8a9101';
    //check waybill status when tracking is clicked
    $(".row-tracking-id").click(function(e) {
         //grab courier data from table
         // var tracking_id = e.target.value; 
         var tracking_id = $(this).text();
         var courier_name   = $('#cname_'+tracking_id).text();
         var express_number = $('#enum_'+tracking_id).text();
         $("#get_tracking_id").html(tracking_id);
         $("#get_courier_name").html(courier_name);
         $("#get_express_number").html(express_number);
         var api_courier_name = ''
            if (courier_name === '汇通快递')
            api_courier_name = 'huitongkuaidi';
            else if (courier_name === '申通快递')
            api_courier_name = 'shentong';
            else if (courier_name === 'EMS')
            api_courier_name = 'ems';
            else if (courier_name === '顺丰快递')
            api_courier_name = 'shunfeng';
            else if (courier_name === '圆通快递')
            api_courier_name = 'yuantong';
            else if (courier_name === '韵达快递')
            api_courier_name = 'yunda';
        //validate courier data should not be empty
        if(courier_name === 'Empty' || express_number === 'Empty')
        {
            $('#postal_api_status').html('<h5 class="msg text-warning orange"><i class="icon-warning-sign"></i> Empty express company or express number</h5>');
            $('#postal_api_tracking').html('');
        }
        else
        {
            //if courier name is ems,shunfeng and shentong use html api
            if(api_courier_name === 'ems' || api_courier_name ==='shunfeng' || api_courier_name === 'shentong')
            {
                request = $.ajax({
                    type: "GET",
                    url: url+'postapi/html/'+tracking_id,
                });
                $('#postal_api_tracking').html('');
                $('#postal_api_status').html('<i class="icon-eye-open icon-spin"></i> Loading......');
                request.done(function (response){
                    if(response.src === false)
                    {
                        $('#postal_api_status').html('<h5 class="msg text-warning orange"><i class="icon-warning-sign"></i> Server Internal Error, Please retry it</h5>');
                        $('#postal_api_tracking').html('');
                    }
                    else
                    {
                        var iframe = '<iframe src="'+response.src+'" width="535" height="270" type="text/html" scrolling="no" id="api_iframe"><iframe> ';
                        $('#postal_api_tracking').html('');
                        $('#postal_api_status').html(iframe);
                        //erase iframe's src once the status modal is closed
                        erase_sr();
                    }
                });
                request.fail(function (response){
                    $('#postal_api_status').html('<h5 class="msg text-warning orange">'+
                        '<i class="icon-warning-sign"></i> Server Internal Error, Please Retry It or check your internet connection</h5>');
                });
           }
           //using regular postal api returned with json type data
           else
           {
                var full_apiurl = apiurl + '?id='+AppKey+'&com='+api_courier_name+'&nu='+express_number; 
                $.ajax( {
                    type: 'GET',
                    url: full_apiurl+'&show=0&muti=1&order=asc',
                    async: false,
                    jsonpCallback: 'jsonCallback',
                    contentType: "application/json",
                    dataType: 'jsonp',
                    beforeSend: function() {
                        $('#postal_api_status').html('<i class="icon-eye-open icon-spin"></i> Loading......');
                        $('#postal_api_tracking').html('');
                        },
                    success: function(data) {
                       if(data.status == 1)
                        {
                           $('#postal_api_status').html('');
                           var state = 'loading...';
                              if(data.state == 0)
                              state = '在途中';
                              else if(data.state == 1) 
                              state = '已发货';
                              else if(data.state == 2) 
                              state = '疑难件';
                              else if(data.state == 3)
                                state  = '已签收';
                            //call this function and save waybill status(returned as state from postal api) into database 
                            auto_update_waybill_status(tracking_id,state);
                            //display the returned information
                            var api_status = "<tr><td><h5>快递单状态:<h5></td> <td><h4>" + state+"</h4></td>";
                            var info ='';
                            $.each(data.data, function(key, val) {
                                info += '<tr> <td>'+val.time+'</td> <td>'+val.context+'</td> </tr>'
                            });
                            var info = '<table class="table table-condensed">'+api_status+''+info +'</table>'
                            $('#postal_api_tracking').html(info);
                        }
                        else
                        {
                            $('#postal_api_status').html(data.message);
                        }
                    },
                    error: function() {
                        $('#postal_api_status').html("Server Internal Error, Please Check Your Internt Connetction And Retry It");
                    }
                });
            }
        }
    });
return false;
}
//this function enable user to editing inline
function inline_editing(waybills)
{
    // Setup default couriers
    var default_couriers = [
        {value: ' ', text: ''},
        {value: 'huitongkuaidi', text: '汇通快递'},
        {value: 'yuantong', text: '圆通快递'},
        {value: 'yunda', text: '韵达快递'},
        {value: 'ems', text: 'EMS'},
        {value: 'shentong', text: '申通快递'},
        {value: 'shunfeng', text: '顺丰快递'}
    ];

    //inline editing for courier name
    $('.row-courier-name').each(function () {
        var $this = $(this);

        $this.editable ({
            type:  'select',
            send: 'always', // Always send request
            source: default_couriers, // set data source for dropdown
            value: $this.data('courier-value'), // set default value for dropdown
            showbuttons: false, // hide the buttons - automatic submit
            url: url + 'waybills/' + $this.data('tracking-id'), 
            title: 'Select Express Company',
            params: function (params) { // set request's values
                var waybill = get_waybill(waybills, $this.data('tracking-id'));
                // set waybill attribute to new value
                waybill.courier_value = params.value;
                // setup custom request
                return JSON.stringify(waybill);
            }
        });
    });

    //inline editing for express number
    $('.row-express-number').each(function () {
        var $this = $(this);

        $this.editable({
            type:  'text',
            pk: $this.data('tracking-id'),
            url: url + 'waybills/' + $this.data('tracking-id'),
            title: 'Enter Express Number',
            params: function (params) {
                var waybill = get_waybill(waybills, $this.data('tracking-id'));
                // set waybill attribute to new value
                waybill.express_number = params.value;
                // setup custom request
                return JSON.stringify(waybill);
            }
        });
    });
}

function get_waybill(waybills, tracking_id)
{
    var obj;
    $.each (waybills, function(key, waybill) {
        if (waybill.tracking_id === tracking_id)
        {
            obj =  waybill;
            return;
        }
    });
    return obj;
}

function search()
{
    var couriers = [ '汇通快递', '申通快递', 'EMS', '顺丰快递', '圆通快递', '韵达快递' ];
    $('#search-form').find('input[name=courier]').typeahead({ source: couriers });

    var cities = ['HK', 'SH', 'CQ', 'TJ'];
    $('#search-form').find('input[name=arrival]').typeahead({ source: cities });

    $('#search-form').submit(function(ev) {
        var $this  = $(this);
        var fields = $(ev.currentTarget).serializeObject();

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
        
        var search_path = url + 'waybills?order_date=' + fields.order_date 
                              + '&location=' + fields.location 
                              + '&tracking_id=' + fields.tracking_id 
                              + '&courier=' + fields.courier
                              + '&flight_date=' + fields.flight_date
                              + '&arrival=' + fields.arrival;
        
        // Check empty fields
        var empty = $this.find(':text').filter(function () { return this.value != ""; });
        if (empty.length)
        {
            $.ajax({
                url: search_path,
                dataType: 'json',
                type: 'get',
                beforeSend: function () {
                    $('.icon-eye-open').addClass('icon-spin');
                    $this.find('.msg').html('Loading ...');
                },
                complete: function (xhr) {
                    $('.icon-eye-open').removeClass('icon-spin');
                    var response = JSON.parse(xhr.responseText);
                    switch (xhr.status)
                    {
                        case 200:
                            $this.find('.msg').html('');
                            load(response, true);
                        break;
                        case 404: $this.find('.msg').html(response.error); break;
                        default:  $this.find('.msg').html('Internal Server Error'); break;
                    }
                }
            });
        }

        return false;
    });
}

//inline editing for waybill status
function update_waybill_status(waybills,state)
{
    $('.row-waybill-status').each(function () {
        var $this = $(this);
        $this.editable({
            type:  'text',
            pk: $this.data('tracking-id'),
            url: url + 'waybills/' + $this.data('tracking-id'), 
            title: 'Enter Waybill Status',
            params: function (params) {
                var waybill = get_waybill(waybills, $this.data('tracking-id'));
                // set waybill attribute to new value
                waybill.waybill_status = params.value;
                if(params.value == "已签收")
                    $('#st_'+$this.data('tracking-id')).addClass('label label-large label-success arrowed-in');
                else
                {
                    $('#st_'+$this.data('tracking-id')).attr('style', 'background-color: rgba(0, 0, 0, 0);');
                    $('#st_'+$this.data('tracking-id')).removeClass('label label-large label-success arrowed-in');
                }
                // setup custom request
                return JSON.stringify(waybill);
            }
        });
    });
}
//this function auto update waybill status upon postal api state
function auto_update_waybill_status(tracking_id,state)
{
    var data = { tracking_id: tracking_id, status: state }
    $.ajax({
        type: "POST",
        url: url+"postapi",
        data: { data: data},
    })
    .done(function(){
        $('#st_'+tracking_id+'').text(state);
        $('#st_'+tracking_id+'').removeClass('editable-empty');
        $('#st_'+tracking_id+'').addClass('label label-large label-success arrowed-in');
    })
    .fail(function() {
        $('#st_'+tracking_id+'').text('');
    });
}

//this function clean the iframe src when user close the status modal
function erase_sr()
{
    $('#close_check_status').click(function(){
        $("#api_iframe").attr('src','');
    });
}

//this function add flag into waybill status
function add_waybill_flag()
{
    $('.row-waybill-status').each(function () {
        var $this = $(this);
        if($(this).text() === "已签收")
        $('#st_'+$this.data('tracking-id')).addClass('label label-large label-success arrowed-in');
    });
}

//refresh button to reload the page
$("#refresh").click(function(){
    location.reload();
});

$("#batch_check_status").click(function() {
    //sorting checking status waybill object into array
    $(".icon-cloud-download").addClass('icon-spin');
    $.ajax({
      url: url+ 'postapi/batch_check',
      type: 'GET',
      complete: function(data) {
        //called when complete
      },
      success: function(data) {
        //called when successful
        $(".icon-cloud-download").removeClass('icon-spin');
        alert(data.Message);
        location.reload();
      },
      error: function(data) {
        //called when there is an error
        alert('未知错误,请重试或检查网络链接');
      }
    });
});
