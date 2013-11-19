$.ajaxSetup({ cache: false });

// Scale bitmap
createjs.Bitmap.prototype.setWidth = function(w)
{       
    if (this.image.width == 0) return;
    
    this.scaleX = w / this.image.width;
}
createjs.Bitmap.prototype.setHeight = function(h)
{
    if (this.image.height == 0) return;

    this.scaleY = h / this.image.height;
}

function rotate_degree(obj) {
    var matrix = obj.css("-webkit-transform") ||
    obj.css("-moz-transform")    ||
    obj.css("-ms-transform")     ||
    obj.css("-o-transform")      ||
    obj.css("transform");
    if(matrix !== 'none') {
        var values = matrix.split('(')[1].split(')')[0].split(',');
        var a = values[0];
        var b = values[1];
        var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
    } else { var angle = 0; }
    return (angle < 0) ? angle +=360 : angle;
}

jQuery.fn.outerHTML = function() {
    return jQuery('<div />').append(this.eq(0).clone()).html();
};

// ROTATE PHOTO
var value = 0;
$('.btn-rotate').click(function (ev){
    value += 180;
    var crop_image = $('.crop-image');
    crop_image.rotate({animateTo:value});
});

// DRAG EFFECT
$('.profile-avatar-modal').draggable();
$('.cropper-overlay').draggable({
    revert: true,
    scroll: false,
    create: function (ev, ui) {
        this.crop_image          = $('.crop-image');
        this.crop_image_pos_top  = '';
        this.crop_image_pos_left = '';
    },
    drag: function (ev, ui) {

        var $this = $(this);
        var top   = $this.position().top;
        var left  = $this.position().left;
        var current_pos_top = '';
        var current_pos_left = '';

        if (this.crop_image_pos_top === '' || this.crop_image_pos_left === '')
        {
            current_pos_top  = (top + (-280)) + 'px';
            current_pos_left = (left + 5) + 'px';
        }
        else
        {
            current_pos_top  = (top + parseInt(this.crop_image_pos_top)) + 'px';
            current_pos_left = (left + parseInt(this.crop_image_pos_left)) + 'px';
        }
        this.crop_image.css('top', current_pos_top);
        this.crop_image.css('left', current_pos_left);
    },
    stop: function (ev, ui) {
        this.crop_image_pos_top  = this.crop_image.css('top');
        this.crop_image_pos_left = this.crop_image.css('left');
    }
});

// ZOOM IN OUT PHOTO
$('.cropper-slider').slider({
    create: function (ev, ui) {
        this.crop_image = $('.crop-image');
    },
    start: function (ev, ui) {
        this.crop_image_pos_top  = parseInt(this.crop_image.css('top'));
        this.crop_image_pos_left = parseInt(this.crop_image.css('left'));
    },
    slide: function (ev, ui) {
        var slider_value = $(this).slider('option', 'value');

        var new_left = (this.crop_image_pos_left - (slider_value/100 +1)) + 'px';
        var new_top  = (this.crop_image_pos_top - (slider_value/100 + 1)) + 'px';
        this.crop_image.css('top', new_top);
        this.crop_image.css('left', new_left);

        var new_height = (((slider_value/100) + 1) * 240) + 'px';
        var new_width  = (((slider_value/100) + 1) * 310) + 'px';
        this.crop_image.css('height', new_height);
        this.crop_image.css('width', new_width);
    }
});

// SHOW UPLOAD MODAL
function display_upload_modal(image_render)
{
    $('.profile-avatar-modal').show();
    $('#overlay').show();
    document.getElementById('crop-image').src = image_render;

    var image = new Image();
    image.src = image_render;
    var data;

    // Submit modal
    $('.modal-footer').submit(function(ev) {
        var degree = parseInt(rotate_degree($('.crop-image')));

        // If image's angle is rotated
        if (degree != 0)
        {
            var crop_image = $('.crop-image');

            // Record the original image height, width, position
            var crop_image_width  = parseInt(crop_image.css('width'));
            var crop_image_height = parseInt(crop_image.css('height'));
            var crop_image_top    = crop_image.css('top');
            var crop_image_left   = crop_image.css('left');

            // Create a temporary canvas to hold the new image to be cloned
            var canvas    = document.createElement('canvas');
            canvas.width  = crop_image_width;
            canvas.height = crop_image_height;

            // Draw canvas with a clone image -> return a bitmap
            var stage = new createjs.Stage(canvas);
            var bitmap = new createjs.Bitmap(image);

            // Rotate the bitmap
            bitmap.setWidth(crop_image_width);
            bitmap.setHeight(crop_image_height);
            bitmap.rotation = 180;
            bitmap.x = crop_image_width;
            bitmap.y = crop_image_height;
            stage.addChild(bitmap);
            stage.update();

            // convert the bitmap back to image
            var tmp = new Image();
            tmp.src = canvas.toDataURL();

            // replace the old image with the new rotated image
            crop_image.attr('src', canvas.toDataURL());

            // crop the new image
            crop();
        }
        else
        {
            // crop the orginal image
            crop();
        }
        return false;
    });

    // Close Modal
    $('.profile-image-cancel').click(function () {
        hide_upload_modal();
    });
}

// CROP FUNC
function crop() 
{
    html2canvas($('.crop-zone'), {
        onrendered: function(canvas) {
            var data = canvas.toDataURL().replace(/^data:image\/(png|jpg|gif);base64,/, '');
            
            $.ajax({
                type: 'post',
                url: url + 'senders/edit_photo',
                data: { photo: data },
                beforeSend: function () {
                    $('.profile-avatar-modal').hide();
                    $('#overlay').hide();
                    $('#msg').removeClass('text-success').addClass('text-error').html('Loading...');
                },
                success: function (response) {
                    var photo_preview = $('#photo-preview img');
                    var photo_url     = (url + response.url).replace('/index.php', '');
                    photo_preview.slideDown(1000);
                    photo_preview.attr('src', photo_url);
                    $('#photo-value').val(response.url);
                    $('#msg').removeClass('text-success text-error').html('');
                }
            });
        }
    });
}

// HIDE UPLOAD MODAL
function hide_upload_modal()
{

    var photo_preview = $('#photo-preview img');
    if (photo_preview.attr('src') == '')
    {
        $('.profile-avatar-modal').hide();
        $('#overlay').hide();
        clear_input_file();
    }
    else
    {
        $('.profile-avatar-modal').hide();
        $('#overlay').hide();
    }
}

// CLEAR FILEUPLOAD
function clear_input_file() 
{
    var input = $('input[type=file]');
    var inputClone = input.clone(true);
    input.after(inputClone);
    input.remove();
    input = inputClone;

    var fileupload = $('.fileupload');
    fileupload.addClass('fileupload-new').removeClass('fileupload-exists');
    fileupload.find('.fileupload-preview').empty();
    $('input[name=photo_value]').val('');
    $('input[name=photo_type]').val('');
}

$('input[type=file]').change(function () {
    var $this  = $(this);
    var reader = new FileReader(); 
    // Accept only graphics
    var filter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;

    reader.onload = function (ev) {
        display_upload_modal(ev.target.result);
    };

    try
    {
        var file = $this.get(0).files[0];
        if (!filter.test(file.type)) {
            alert ('You must select a valid image');
            clear_input_file();
            return;
        }
    } catch (err){ }

    reader.readAsDataURL(file);
});

$('input[name=tracking_id]').focusout(function(){
    var $this       = $(this);
    var tracking_id = $this.val() ? $this.val() : 0;

    $.ajax({
        url: url + 'senders/' + tracking_id,
        dataType: 'json',
        type: 'get',
        beforeSend: function () {
            $this.next().show();
        },
        complete: function (xhr) {
            $this.next().hide();
            var response = JSON.parse(xhr.responseText);
            if (xhr.status === 200)
            {
                $this.parent('p').removeClass('control-group error');
                $('input[name=sender_id]').val(response[0].id);
                $('#msg').removeClass('text-success text-error').html('');
            }
            else
            {
                $this.parent('p').addClass('control-group error');
                $('#msg').removeClass('text-success').addClass('text-error').html(response.error);
                $('input[name=sender_id]').val('');
            }
        }
    });
});


$('input[name=identity]').focusout(function(){
    if ($(this).val() !== '')
        $(this).parent('p').removeClass('control-group error');
    else
        $(this).parent('p').addClass('control-group error');
});

// UPLOAD PHOTO
$('#upload-form').submit(function(ev) {
    var $this         = $(this);
    var sender_detail = $(ev.currentTarget).serializeObject();
    var sender_id     = sender_detail.sender_id;

    if ($('input[name=tracking_id]').val() !== ''
        && $('input[name=identity]').val() !== ''
        && $('input[name=photo_value]').val() !== ''
        && $('input[name=sender_id]').val() !== '')
    {
        $.ajax({
            type: 'post',
            url: url + 'senders/' + sender_id,
            data: { sender_detail: sender_detail },
            beforeSend: function () {
                $('#msg').removeClass('text-success').addClass('text-error').html('Loading...');
            },
            success: function (response) {
                clear_input_file();
                remove_photo_preview();
                $this.find('p').removeClass('control-group error');
                $('#msg').removeClass('text-error').addClass('text-success').html('You information is safely saved into our database.');
            },
            error: function (response) {
                $this.find('p').addClass('control-group error');
                $('#msg').removeClass('text-success').addClass('text-error').html('Sorry ! Internal Server Error');
            }
        });
    }
    else
    {
        $this.find('p').addClass('control-group error');
        if ($('input[name=sender_id]').val() === '')
        {
            $('#msg').removeClass('text-success').addClass('text-error').html('Sender Not Found');
            $('input[name=sender_id]').focus();
        }
        else
        {
            $('.fileupload').effect('shake');
            $('#msg').removeClass('text-success').addClass('text-error').html('Please enter required fields and upload at least one photo');
        }
    }

    return false;
});

// REMOVE PHOTO PREVIEW
$('#remove-preview').click(function(ev) {
    remove_photo_preview();
});

function remove_photo_preview()
{
    var photo_preview = $('#photo-preview img');
    photo_preview.slideUp('fast');
    photo_preview.attr('src', '');
    $('#msg').removeClass('text-success text-error').html('');
}

// CHANGE UPLOAD PHOTO TYPE
$('.photo-type').click(function(ev) {
    var $this = $(this);
    var photo_preview = $('#photo-preview img');
    var photo_type = $this.data('label');

    if (photo_preview.attr('src') == '')
    {
        $('#label-photo-type').html($this.html() + ':');
        $('#photo-type').val(photo_type);
    }
    else
    {
        photo_preview.effect('shake');
        $('#msg').removeClass('text-success').addClass('text-error').html('Please upload this photo first before move on to the other photos');
    }
});
