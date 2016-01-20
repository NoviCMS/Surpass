<script type="text/javascript">

    var TU{!! $dir_studly !!} = {

        ids: {
            input: '{!! $input_id !!}',
            preview: '{!! $preview_id !!}'
        },
        maxFile: 0,
        formData: {},
        previewParameters: {},
        processingFile: 0,
        loadData: [],
        imageOrder: 1,
        overCallback: null,
        overCallbackFlag: false,
        progress: '',
        overwriteFlag: {!! ($overwrite) ? 'true' : 'false' !!},
        overwritePreviewBox: null,
        @if(!empty($drop_zone_id))
        dropZone: $('#{!! $drop_zone_id !!}'),
        @endif

        init: function() {

            if(TU{!! $dir_studly !!}.loadData.length > 0) {

                TU{!! $dir_studly !!}.initialPreview();

            }

            $('#'+ TU{!! $dir_studly !!}.ids['input']).fileupload({
                dataType: 'json',
                @if(!empty($resize_params['size']))
                disableImageResize: false,
                @endif
                @if(!empty($resize_params['size']['maxWidth']))
                imageMaxWidth: {!! intval($resize_params['size']['maxWidth']) !!},
                @endif
                @if(!empty($resize_params['size']['maxHeight']))
                imageMaxHeight: {!! intval($resize_params['size']['maxHeight']) !!},
                @endif
                @if(!empty($resize_params['force_crop']))
                imageCrop: {!! ($resize_params['force_crop']) ? 'true' : 'false' !!},
                @endif
                @if($timeout > 0)
                timeout: {!! $timeout !!},
                @endif
                add: function(e, data) {

                    var fileType = data.files[0].type;

                    if(fileType.indexOf('image/') === 0) {

                        if(!TU{!! $dir_studly !!}.isFull()) {
                            var allowupload = true;
                        }else if(TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] > 0){
                            var allowupload = true;
                        }else if(TU{!! $dir_studly !!}.overCallbackFlag && $.isFunction(TU{!! $dir_studly !!}.overCallback)) {

                            TU{!! $dir_studly !!}.overCallback();
                            TU{!! $dir_studly !!}.overCallbackFlag = false;

                        }

                        if(allowupload == true) {
                            if(TU{!! $dir_studly !!}.progress != '') {

                                if(!(TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] > 0)) {
                                    TU{!! $dir_studly !!}.processingFile++;
                                    TU{!! $dir_studly !!}.resizeheight();
                                }

                                if(TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] > 0) {
                                    var loadingBox = tmpl('loading_box_change_{!! $dir !!}', {content: TU{!! $dir_studly !!}.progress});
                                    $('.{!! $id_hidden_name !!}[value='+TU{!! $dir_studly !!}.formData['surpass_overwrite_id']+']').parent().parent().parent().empty().append(loadingBox);
                                }else{
                                    var loadingBox = tmpl('loading_box_{!! $dir !!}', {content: TU{!! $dir_studly !!}.progress});
                                    $('#' + TU{!! $dir_studly !!}.ids['preview']).append(loadingBox);
                                }

                            }

                            {!! (!empty($callbacks['upload'])) ? $callbacks['upload'] : '' !!}
                            $.blueimp.fileupload.prototype.options.add.call(this, e, data);
                            data.submit();
                        }

                    } else {

                        {!! (!empty($callbacks['file_type_error'])) ? $callbacks['file_type_error'] : '' !!}

                    }

                },
                error: function(e, data) {

                    {!! (!empty($callbacks['timeout'])) ? $callbacks['timeout'] : '' !!}
                    this.done(e, {result: {result: false}, files: []});

                },
                change: function (e, data) {

                    TU{!! $dir_studly !!}.overCallbackFlag = true;

                },
                drop: function (e, data) {

                    TU{!! $dir_studly !!}.overCallbackFlag = true;

                },
                done: function (e, data) {

                    $('#'+ TU{!! $dir_studly !!}.ids['preview'] +' .box-image:not(.used) > :first').parent().addClass("used").attr('id', data['result']['insertId']);


                    if(TU{!! $dir_studly !!}.progress != '') {

                        $.each($('#'+ TU{!! $dir_studly !!}.ids['preview']).children(), function(index, child){

                            if(!$(child).find('.{!! $id_hidden_name !!}').length) {

                                $(child).find('.box-body').empty();
                                return false;

                            }

                        });

                    }

                    var file = (typeof(data.files[0]) == 'undefined') ? null : data.files[0];

                    if(file != null && data['result']['result']) {

                        loadImage(file, function (img) {

                            if(img.type != 'error') {
                                TU{!! $dir_studly !!}.preview(
                                        img,
                                        data['result']['insertId'],
                                        file.name,
                                        data['result']['saveMode'],
                                        data['result']['file_path'],
                                        data['result']['filename']
                                );
                                {!! (!empty($callbacks['done'])) ? $callbacks['done'] : '' !!}

                            }

                        }, TU{!! $dir_studly !!}.previewParameters, data['result']['file_path']);

                    } else {

                        TU{!! $dir_studly !!}.processingFile--;
                        {!! (!empty($callbacks['failed'])) ? $callbacks['failed'] : '' !!}

                    }

                }

            }).bind('fileuploadsubmit', function (e, data) {

                data.formData = TU{!! $dir_studly !!}.formData;

            });

        },
        preview: function(img, id, filename, saveMode, file_path, new_filename) {




            if(!file_path){
                var file_path = $(img).attr('src');
            }else{
                var file_path = '/' + file_path;
                var filename = new_filename;
            }



            var previewBox = tmpl('preview_box_{!! $dir !!}', {});
            var previewFooter = tmpl('preview_footer_{!! $dir !!}', {surpassId: id,img: file_path});
            var hiddenObj = $('.{!! $id_hidden_name !!}[value="'+ id +'"]');

            if(hiddenObj.length) {

                hiddenObj.remove();

            }

            var content = $(previewBox).append(img).append(previewFooter);

            if(saveMode == 'overwrite') {

                var originalObj = TU{!! $dir_studly !!}.overwritePreviewBox;

                originalObj.prepend('<div class="box-header with-border"></div>');
                originalObj.find('.box-body').removeAttr('style').append(content);

                $('#'+ TU{!! $dir_studly !!}.ids['preview']).find(content)
                        .css({'text-align' : 'center', 'height' : '254px'});

                $('#'+ TU{!! $dir_studly !!}.ids['preview']).find(content).find('img')
                        .wrap('<div class="col-md-12" style="height:200px; margin-bottom:20px;"></div>')
                        .css({
                            'width' : 'auto',
                            'height' : 'auto',
                            'max-height' : '200px',
                            'max-width' : '100%',
                            'vertical-align' : 'middle'
                        })
                        .parent().prepend('<span class="helper" style="display: inline-block; height: 100%; vertical-align: middle;"></span>')
                        .parent().parent().parent().parent().find('.box-header').empty()
                        .append('<h3 class="box-title">'+ filename +'</h3>')


            } else {



                $('#'+ TU{!! $dir_studly !!}.ids['preview'] +' #'+ id).find('.box-body').empty();

                $('#'+ TU{!! $dir_studly !!}.ids['preview'] +' #'+ id).find('.box-body').parent().prepend('<div class="box-header with-border"></div>');
                $('#'+ TU{!! $dir_studly !!}.ids['preview'] +' #'+ id).find('.box-body').removeAttr('style').append(content);

                var divCol = $('#'+ TU{!! $dir_studly !!}.ids['preview']).find(content).parent().parent().parent();

                divCol.find('.images')
                        .css({'text-align' : 'center'});

                divCol.find('.images').find('img')
                        .wrap('<div class="col-md-12" style="height:'+TU{!! $dir_studly !!}.previewParameters.maxHeight+'; margin-bottom:20px;"></div>')
                        .css({
                            'width' : 'auto',
                            'height' : 'auto',
                            'max-height' : TU{!! $dir_studly !!}.previewParameters.maxHeight,
                            'max-width' : TU{!! $dir_studly !!}.previewParameters.maxWidth,
                            'vertical-align' : 'middle'
                        }).parent().prepend('<span class="helper" style="display: inline-block; height: 100%; vertical-align: middle;"></span>');

                divCol.find('.box-header')
                        .append('<h3 class="box-title">'+ filename +'</h3>')

            }

            {!! (!empty($callbacks['load'])) ? $callbacks['load'] : '' !!}

            TU{!! $dir_studly !!}.resizeheight();
            TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] = 0;
            TU{!! $dir_studly !!}.imageOrder++;
            $('#preview_images').trigger('sortupdate');

        },
        resizeheight: function() {

            if(TU{!! $dir_studly !!}.processingFile >= 1){

                processingfilenr = TU{!! $dir_studly !!}.processingFile

                if($( window ).width() < "751") {
                    $("#preview_images").height(function (index, height) {
                        return ($(".box-image").height() * (processingfilenr));
                    });
                }else if($( window ).width() < "975") {
                    $("#preview_images").height(function (index, height) {
                        return ($(".box-image").height() * Math.ceil((processingfilenr) / 2));
                    });
                }else if($( window ).width() >= "975") {

                    $("#preview_images").height(function (index, height) {
                        return ($(".box-image").height() * Math.ceil((processingfilenr) / 3));
                    });
                }
            }else{
                $("#preview_images").height(function (index, height) {
                    return (0);
                });
            }

        },
        initialPreview: function() {

            TU{!! $dir_studly !!}.processingFile = TU{!! $dir_studly !!}.loadData.length;
            $.each(TU{!! $dir_studly !!}.loadData, function(key, loadValues){
                var id = loadValues['id'];
                var url = loadValues['url'];
                var filename = loadValues['filename'];
                var img = $('<img/>', {
                    src: url
                });
                var loadingBox = tmpl('loading_box_{!! $dir !!}', {content: TU{!! $dir_studly !!}.progress, imageid: id});
                $('#' + TU{!! $dir_studly !!}.ids['preview']).append(loadingBox);
                $('#'+ TU{!! $dir_studly !!}.ids['preview'] +' #'+ id).addClass("used");
                loadImage(url, function (img) {
                            TU{!! $dir_studly !!}.preview(img, id, filename)
                        }, TU{!! $dir_studly !!}.previewParameters
                );
            });

        },
        remove: function(self, id) {

            $(self).addClass('disabled');

            var removeUrl = $('#'+ TU{!! $dir_studly !!}.ids['input']).data('removeUrl');
            var formData = TU{!! $dir_studly !!}.formData;
            formData['remove_id'] = id;

            $.post(removeUrl, TU{!! $dir_studly !!}.formData, function(data){

                if(data['result']) {

                    TU{!! $dir_studly !!}.removeBox($(self).parent().parent().parent().parent().parent());


                }
                {!! (!empty($callbacks['remove'])) ? $callbacks['remove'] : '' !!}

            }, 'json');

            return false;

        },
        removeBox: function(targetObj) {

            targetObj.remove();
            TU{!! $dir_studly !!}.processingFile--;
            TU{!! $dir_studly !!}.imageOrder--;
            TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] = -1;
            TU{!! $dir_studly !!}.overwritePreviewBox = null;

            TU{!! $dir_studly !!}.resizeheight();

            $('#preview_images').trigger('sortupdate');

        },
        overwrite: function(self, container, targetId) {

            $(self).addClass('disabled');

            TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] = targetId;
            TU{!! $dir_studly !!}.overwritePreviewBox = $(container);
            $('#'+ TU{!! $dir_studly !!}.ids['input']).click();
            TU{!! $dir_studly !!}.imageOrder--;

            $(self).removeClass('disabled');

        },
        isFull: function() {

            var processingFileCount = TU{!! $dir_studly !!}.processingFile;
            var maxFileCount = TU{!! $dir_studly !!}.maxFile;

            if(TU{!! $dir_studly !!}.formData['surpass_overwrite_id'] > 0
                    && processingFileCount <= maxFileCount) {

                return false;

            } else if(processingFileCount < maxFileCount) {

                return false;

            }

            return true;

        }

    };

</script>
<script type="text/x-tmpl" id="preview_box_{!! $dir !!}">
	<div{!! $css_div !!}></div>
</script>
<script type="text/x-tmpl" id="loading_box_{!! $dir !!}">
    <div class="col-md-4 col-sm-6 col-xs-12 box-image" @shield('admin.product.edit') style="cursor: all-scroll;" @endshield id="{%#o.imageid%}">
        <div class="box box-success box-solid">
            <div class="box-body" style="height:315px">
                {%#o.content%}
            </div>
        </div>
    </div>
</script>
<script type="text/x-tmpl" id="loading_box_change_{!! $dir !!}">
            <div class="box-body" style="height:315px">
                {%#o.content%}
            </div>
</script>
<script type="text/x-tmpl" id="preview_footer_{!! $dir !!}">
    @shield('admin.product.edit')
    <input class="{!! $id_hidden_name !!}" type="hidden" name="{!! $id_hidden_name !!}[]" value="{%=o.surpassId%}">
    <div class="col-md-4 col-sm-6 col-xs-12">
        <button{!! $css_changebutton !!} type="button" imageid="{%=o.surpassId%}" onclick="return TU{!! $dir_studly !!}.overwrite(this, this.parentNode.parentNode.parentNode.parentNode, {%=o.surpassId%});">Verander</button>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <button{!! $css_editbutton !!} type="button" onclick="openResizeCropModal(this,{%=o.surpassId%},'{%=o.img%}')" >bewerk</button>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <button{!! $css_deletebutton !!} type="button" onclick="return TU{!! $dir_studly !!}.remove(this, {%=o.surpassId%});">{!! $button_label !!}</button>
    </div>
    @endshield
</script>
<script>

    $(document).ready(function(){

        TU{!! $dir_studly !!}.ids = {
            input: '{!! $input_id !!}',
            preview: '{!! $preview_id !!}'
        };
        TU{!! $dir_studly !!}.maxFile = {!! $max_file !!};

        @if(!empty($load_data))

        TU{!! $dir_studly !!}.loadData = {!! json_encode($load_data) !!};

        @endif

        @if(!empty($form_data))

        TU{!! $dir_studly !!}.formData = {!! json_encode($form_data) !!};

        @endif

        @if(!empty($preview_params))

        TU{!! $dir_studly !!}.previewParameters = {!! json_encode($preview_params) !!};

        @endif

        @if(!empty($progress))

        TU{!! $dir_studly !!}.progress = '{!! $progress !!}';

        @endif

        TU{!! $dir_studly !!}.overCallback = function(){
            alert('{!! $alert !!}');
        };
        TU{!! $dir_studly !!}.init();

        $( window ).resize(function() {
            TU{!! $dir_studly !!}.resizeheight();
        });


        @shield('admin.product.edit')

        var cropBoxData;
        var canvasData;

        $('.resizeCropModal').on('shown.bs.modal', function () {

            var $image = $('#resizeCropimage');

            $image.cropper({
                aspectRatio: {!! Setting::get('products.images.ResizeAspectRatio') !!},
                viewMode: {!! Setting::get('products.images.ResizeViewMode') !!},
                responsive: {!! Setting::get('products.images.ResizeResponsive') !!},
                autoCropArea: {!! Setting::get('products.images.ResizeAutoCropArea') !!},
                built: function () {
                    $image.cropper('setCanvasData', canvasData);
                    $image.cropper('setCropBoxData', cropBoxData);
                },
                zoom: function (e) {
                    if (e.ratio > 10) {
                        e.preventDefault();
                        $(this).cropper('zoomTo', 10);
                    }
                    if (e.ratio < 0.1) {
                        e.preventDefault();
                        $(this).cropper('zoomTo', 0.1);
                    }
                }
            });

        });

        $('.resizeCropModal').on('hidden.bs.modal', function () {

            // TODO: make a save button for the popup and do not run if closed

            var $image = $('#resizeCropimage');
            canvasImage = $image.cropper('getCroppedCanvas');
            $image.cropper('destroy');
            var imgid = $('#resizeCropimage').attr('imgid');
            var input = $('#'+ TU{!! $dir_studly !!}.ids['input']);
            var uploadUrl = $('#'+ TU{!! $dir_studly !!}.ids['input']).attr('data-url');
            var fileName = "upload.png";

            var loadingBox = tmpl('loading_box_change_{!! $dir !!}', {content: TU{!! $dir_studly !!}.progress, imageid: imgid});
            $('.{!! $id_hidden_name !!}[value='+imgid+']').parent().parent().parent().empty().append(loadingBox).parent().removeClass("used");

            if (canvasImage.toBlob) {
                canvasImage.toBlob(
                        function (blob) {

                            var formData = new FormData();
                            formData.append('image_upload', blob, fileName);
                            formData.append('_token', '{{ csrf_token() }}');
                            formData.append('surpass_overwrite_id', imgid);

                            formData.append('surpass_hidden_dir', "products");

                            $.ajax({
                                url: uploadUrl,
                                data: formData,
                                processData: false,
                                contentType: false,
                                type: 'POST',
                                success: function(data){

                                    saveMode = "edit";


                                    $('#'+ TU{!! $dir_studly !!}.ids['preview'] +' .box-image:not(.used) > :first').parent().addClass("used").attr('id', data['insertId']);


                                    loadImage(blob, function (img) {

                                        if(img.type != 'error') {
                                            TU{!! $dir_studly !!}.preview(
                                                    img,
                                                    data['insertId'],
                                                    data['filename'],
                                                    saveMode,
                                                    data['file_path'],
                                                    data['filename']
                                            );
                                            {!! (!empty($callbacks['done'])) ? $callbacks['done'] : '' !!}

                                        }

                                    }, TU{!! $dir_studly !!}.previewParameters, data['file_path']);


                                }
                            });


                        },
                        'image/png'
                );
            }


        });

        @endshield

    });

</script>