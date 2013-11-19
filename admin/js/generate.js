/********************************************************************************/
/************************ GENEREATE WAYBILL *************************************/
/********************************************************************************/
$('#generate-waybill-form').submit(function(ev) {
    var $this = $(this);
    var data = $(ev.currentTarget).serializeObject();

    if (data.location === '' 
        || data.format === ''
        || data.begin_number === '')
    {
        $this.find('.control-group').addClass('error');
    }
    else
    {
        $this.find('.control-group').removeClass('error');
        if (data.end_number === '') 
            data.end_number = data.begin_number;

        if (Number(data.begin_number) > Number(data.end_number))
        {
            $('input[name=begin_number]').parent().parent().addClass('error');
            $('input[name=end_number]').parent().parent().addClass('error');
        }
        else
        {
            $this.find('.control-group').removeClass('error');
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: url + 'waybills',
                data: JSON.stringify(data),
                beforeSend: function() {
                    $('.icon-spinner').addClass('icon-spin');
                    $('.icon-bell-alt').removeClass('icon-animated-bell');
                },
                complete: function (response) {
                    $this.find('.control-group').removeClass('error');
                    $('.icon-spinner').removeClass('icon-spin');
                    $('.icon-bell-alt').addClass('icon-animated-bell');

                    if (response.status === 200)
                    {
                        var context = $.parseJSON(response.responseText);
                        context[0].download_link = (url + context[0].download_link).replace('/index.php', '')
                        var source = $('#notification-template').html();
                        var template = Handlebars.compile(source);
                        $('#notifications').append(template(context[0]));
                    }
                    else
                    {
                        var context = {tracking_id: response.responseText};
                        var source = $('#notify-error').html();
                        var template = Handlebars.compile(source);
                        $('#notifications').append(template(context));
                    }

                }
            });
        }
    }
	return false;
});

$('#lnk-check-log').click(function () {
    $('#logs').find('li').remove();
    var source = $('#log-template').html();
    var template = Handlebars.compile(source);

    $.getJSON(url + 'logs', function (logs) {
        $.each (logs, function (i, log) {
            log.download_link = (url + log.download_link).replace('/index.php', '')
        });
        $('#logs').append(template(logs));
    });
});
