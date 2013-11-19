$.ajaxSetup({ cache: false });

$("#tracking_form").submit(function(ev) {
    var $this         = $(this);
    var tracking_detail = $(ev.currentTarget).serializeObject();
    var trackid     = tracking_detail.tracking_id;
    if (tracking_detail.tracking_id && tracking_detail.phone_number)
    {
        //hide error mesage
        $('#validation').hide('slow');
        $('#errorInput').attr('class','control-group');
        //validate tracking id and dispaly waybill tracking details
        $.ajax({
            type: 'post',
            url: url + 'postapi/api',
            data: { tracking_detail: tracking_detail },
            beforeSend: function () {
                $("#submit_track").text('Loading...');
                $('#tracking_summary').hide();
                $("#waybill-tracking-details").hide();
                // $('#postal_api_summary').hide();
            },
            success: function (data) {
                //return status 1 for invalid tracking id
                if(data.tracking_id === 'incorret_phone')
                {
                    //input trackind id is invalidate
                    $('#postal_api_summary').hide();
                    $('#tracking_summary').show();
                    $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
                    $('#get_status').html('<span class="label label-important">Invalid Phone Number</span>');
                    $('#get_status').addClass("error");
                    $("#submit_track").text('Submit');
                    $('#get_time').hide();
                    $('#get_context').hide();
                    $('#postal_api').attr('src','');
                    //hide tracking details if is invalid tracking id
                    $("#waybill-tracking-details").hide();
                }
                else if(data.tracking_id === 'false')
                {
                    //input trackind id is invalidate
                    $('#postal_api_summary').hide();
                    $('#tracking_summary').show();
                    $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
                    $('#get_status').html('<span class="label label-important">Invalid Tracking ID</span>');
                    $('#get_status').addClass("error");
                    $("#submit_track").text('Submit');
                    $('#get_time').hide();
                    $('#get_context').hide();
                    $('#postal_api').attr('src','');
                    //hide tracking details if is invalid tracking id
                    $("#waybill-tracking-details").hide();
                }
                else{
                    // if has no courier info 
                    if(data.courier == 'false')
                    {
                        //has no local status
                        if (data.local_status == 'false') 
                        {
                            $('#tracking_summary').show();
                            $('#waybill-tracking-details').html('There is no tracking information for this waybill at this moment, please check it later.<br><br>');
                            $("#waybill-tracking-details").show();
                            $("#submit_track").text('Submit');
                            $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
                            $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
                            // $('#postal_api').attr('src','');
                        }
                        else //has no local status
                        {
                            //input trackind id has no postal api info.
                            $('#tracking_summary').show();
                            $('#waybill-tracking-details').show();
                            $("#submit_track").text('Submit');
                            $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
                            $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
                            // $('#postal_api').attr('src','');
                            //loops the local status object
                            var tracking_data = '';
                            $.each(data.local_status, function(key, val) {
                                tracking_data += '<tr><td>'+ val.time +'</td> <td>'+val.context+'</td></tr>';
                            });
                            var table_start = '<table class="table table-condensed">'+ 
                                '<tr class="warning"> <th>Time:</th> <th>Location & Status:</th> </tr>';
                            var table_end = '</table>';
                            $("#waybill-tracking-details").html(table_start+''+tracking_data+''+table_end);
                        }
                    }
                    //input trackind id have postal api info.
                    else{ 
                        //has no local status but postal api status
                        if (data.local_status == 'false') 
                        {
                            api_status_only(data,trackid);
                        }
                        else //has all the status
                        {
                            //return as html link
                            if(data.api_status === 'is_src')
                            {
                                $('#tracking_summary').show();
                                $('#waybill-tracking-details').show();
                                $("#submit_track").text('Submit');
                                $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
                                $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
                                //loops the local status object
                                var tracking_data = '';
                                $.each(data.local_status, function(key, val) {
                                    tracking_data += '<tr><td>'+ val.time +'</td> <td>'+val.context+'</td></tr>';
                                });
                                var table_start = '<table class="table table-condensed">'+ 
                                    '<tr class="warning"> <th>Time:</th> <th>Location & Status:</th> </tr>';
                                var table_end = '</table>';
                                var iframe = '<iframe id="postal_api" src="'+data.src+'" width="535" height="270" type="text/html" frameborder="0" scrolling="no"> </iframe>'
                                $("#waybill-tracking-details").html(table_start+''+tracking_data+''+table_end+iframe);
                            }
                            else //returned as json
                            {
                                $('#tracking_summary').show();
                                $('#waybill-tracking-details').show();
                                $("#submit_track").text('Submit');
                                $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
                                $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
                                //loops the local status object
                                var tracking_data = '';
                                $.each(data.local_status, function(key, val) {
                                    tracking_data += '<tr><td>'+ val.time +'</td> <td>'+val.context+'</td></tr>';
                                });
                                //loops the postal api status object
                                var postal_api_data = '';
                                $.each(data.api_status.data, function(item, value) {
                                    postal_api_data += '<tr><td>'+ value.time +'</td> <td>'+value.context+'</td></tr>';
                                });
                                var table_start = '<table class="table table-condensed">'+ 
                                    '<tr class="warning"> <th>Time:</th> <th>Location & Status:</th> </tr>';
                                var table_end = '</table>';
                                $("#waybill-tracking-details").html(table_start+''+postal_api_data+''+tracking_data+''+table_end);
                            }
                        }
                    }
                }// end of else
            }, 
            error :function (data) {
                $("#submit_track").text('Submit');
                $('#waybill-tracking-details').show();
                $('#waybill-tracking-details').html("Server internal error, Please try again");
            } //end of success
        }); //end of ajax
    }//end of if
    else
    {
        $('#validation').show('slow');
        $('#errorInput').addClass('control-group error');
        $('#tracking_summary').hide();
        $("#waybill-tracking-details").hide();
        if(!tracking_detail.tracking_id)
        $('#tracking_id').focus();
        else if(!tracking_detail.phone_number)
        $('#phone_number').focus();
    }
    return false;
});
//return html link with iframe
function api_status_only(data,trackid)
{
    //return as html link
    if(data.api_status === 'is_src')
    {
        if(data.src == false)
        {
            $('#tracking_summary').show();
            $('#waybill-tracking-details').show();
            $("#submit_track").text('Submit');
            $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
            $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
            $("#waybill-tracking-details").html("Server Internal Error, Please Try Again !");
        }
        else
        {
            $('#tracking_summary').show();
            $('#waybill-tracking-details').show();
            $("#submit_track").text('Submit');
            $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
            $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
            var iframe = '<iframe id="postal_api" src="'+data.src+'" width="535" height="270" type="text/html" frameborder="0" scrolling="no"> </iframe>'
            $("#waybill-tracking-details").html(iframe);
        }
    }
    else //returned as json
    {
        $('#tracking_summary').show();
        $('#waybill-tracking-details').show();
        $("#submit_track").text('Submit');
        $('#get_id').html('<span class="label label-info">'+trackid+'</span>');
        $('#get_status').html('<span class="label label-success">Valid Tracking ID</span>');
        //loops the postal api status object
        var postal_api_data = '';
        $.each(data.api_status.data, function(item, value) {
            postal_api_data += '<tr><td>'+ value.time +'</td> <td>'+value.context+'</td></tr>';
        });
        var table_start = '<table class="table table-condensed">'+ 
            '<tr class="warning"> <th>Time:</th> <th>Location & Status:</th> </tr>';
        var table_end = '</table>';
        $("#waybill-tracking-details").html(table_start+''+postal_api_data+''+table_end);
    }
}
