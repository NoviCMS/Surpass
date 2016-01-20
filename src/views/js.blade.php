<script type="text/javascript">

    var TU{!! $imageObj !!} = {

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

            if(TU{!! $imageObj !!}.loadData.length > 0) {

                TU{!! $imageObj !!}.initialPreview();

            }

            $('#'+ TU{!! $imageObj !!}.ids['input']).fileupload({
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

                        if(!TU{!! $imageObj !!}.isFull()) {
                            var allowupload = true;
                        }else if(TU{!! $imageObj !!}.formData['surpass_overwrite_id'] > 0){
                            var allowupload = true;
                        }else if(TU{!! $imageObj !!}.overCallbackFlag && $.isFunction(TU{!! $imageObj !!}.overCallback)) {

                            TU{!! $imageObj !!}.overCallback();
                            TU{!! $imageObj !!}.overCallbackFlag = false;

                        }

                        if(allowupload == true) {
                            if(TU{!! $imageObj !!}.progress != '') {

                                if(!(TU{!! $imageObj !!}.formData['surpass_overwrite_id'] > 0)) {
                                    TU{!! $imageObj !!}.processingFile++;
                                    TU{!! $imageObj !!}.resizeheight();
                                }

                                if(TU{!! $imageObj !!}.formData['surpass_overwrite_id'] > 0) {
                                    var loadingBox = tmpl('loading_box_change_{!! $dir !!}', {content: TU{!! $imageObj !!}.progress});
                                    $('.{!! $id_hidden_name !!}[value='+TU{!! $imageObj !!}.formData['surpass_overwrite_id']+']').parent().parent().parent().empty().append(loadingBox);
                                }else{
                                    var loadingBox = tmpl('loading_box_{!! $dir !!}', {content: TU{!! $imageObj !!}.progress});
                                    $('#' + TU{!! $imageObj !!}.ids['preview']).append(loadingBox);
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

                    TU{!! $imageObj !!}.overCallbackFlag = true;

                },
                drop: function (e, data) {

                    TU{!! $imageObj !!}.overCallbackFlag = true;

                },
                done: function (e, data) {

                    $('#'+ TU{!! $imageObj !!}.ids['preview'] +' .box-image:not(.used) > :first').parent().addClass("used").attr('id', data['result']['insertId']);


                    if(TU{!! $imageObj !!}.progress != '') {

                        $.each($('#'+ TU{!! $imageObj !!}.ids['preview']).children(), function(index, child){

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
                                TU{!! $imageObj !!}.preview(
                                        img,
                                        data['result']['insertId'],
                                        file.name,
                                        data['result']['saveMode'],
                                        data['result']['file_path'],
                                        data['result']['filename']
                                );
                                {!! (!empty($callbacks['done'])) ? $callbacks['done'] : '' !!}

                            }

                        }, TU{!! $imageObj !!}.previewParameters, data['result']['file_path']);

                    } else {

                        TU{!! $imageObj !!}.processingFile--;
                        {!! (!empty($callbacks['failed'])) ? $callbacks['failed'] : '' !!}

                    }

                }

            }).bind('fileuploadsubmit', function (e, data) {

                data.formData = TU{!! $imageObj !!}.formData;

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

                var originalObj = TU{!! $imageObj !!}.overwritePreviewBox;

                originalObj.prepend('<div class="box-header with-border"></div>');
                originalObj.find('.box-body').removeAttr('style').append(content);

                $('#'+ TU{!! $imageObj !!}.ids['preview']).find(content)
                        .css({'text-align' : 'center', 'height' : '254px'});

                $('#'+ TU{!! $imageObj !!}.ids['preview']).find(content).find('img')
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



                $('#'+ TU{!! $imageObj !!}.ids['preview'] +' #'+ id).find('.box-body').empty();

                $('#'+ TU{!! $imageObj !!}.ids['preview'] +' #'+ id).find('.box-body').parent().prepend('<div class="box-header with-border"></div>');
                $('#'+ TU{!! $imageObj !!}.ids['preview'] +' #'+ id).find('.box-body').removeAttr('style').append(content);

                var divCol = $('#'+ TU{!! $imageObj !!}.ids['preview']).find(content).parent().parent().parent();

                divCol.find('.images')
                        .css({'text-align' : 'center'});

                divCol.find('.images').find('img')
                        .wrap('<div class="col-md-12" style="height:'+TU{!! $imageObj !!}.previewParameters.maxHeight+'; margin-bottom:20px;"></div>')
                        .css({
                            'width' : 'auto',
                            'height' : 'auto',
                            'max-height' : TU{!! $imageObj !!}.previewParameters.maxHeight,
                            'max-width' : TU{!! $imageObj !!}.previewParameters.maxWidth,
                            'vertical-align' : 'middle'
                        }).parent().prepend('<span class="helper" style="display: inline-block; height: 100%; vertical-align: middle;"></span>');

                divCol.find('.box-header')
                        .append('<h3 class="box-title">'+ filename +'</h3>')

            }

            {!! (!empty($callbacks['load'])) ? $callbacks['load'] : '' !!}

            TU{!! $imageObj !!}.resizeheight();
            TU{!! $imageObj !!}.formData['surpass_overwrite_id'] = 0;
            TU{!! $imageObj !!}.imageOrder++;
            $('#preview_images').trigger('sortupdate');

        },
        resizeheight: function() {

            if(TU{!! $imageObj !!}.processingFile >= 1){

                processingfilenr = TU{!! $imageObj !!}.processingFile

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

            TU{!! $imageObj !!}.processingFile = TU{!! $imageObj !!}.loadData.length;
            $.each(TU{!! $imageObj !!}.loadData, function(key, loadValues){
                var id = loadValues['id'];
                var url = loadValues['url'];
                var filename = loadValues['filename'];
                var img = $('<img/>', {
                    src: url
                });
                var loadingBox = tmpl('loading_box_{!! $dir !!}', {content: TU{!! $imageObj !!}.progress, imageid: id});
                $('#' + TU{!! $imageObj !!}.ids['preview']).append(loadingBox);
                $('#'+ TU{!! $imageObj !!}.ids['preview'] +' #'+ id).addClass("used");
                loadImage(url, function (img) {
                            TU{!! $imageObj !!}.preview(img, id, filename)
                        }, TU{!! $imageObj !!}.previewParameters
                );
            });

        },
        remove: function(self, id) {

            $(self).addClass('disabled');

            var removeUrl = $('#'+ TU{!! $imageObj !!}.ids['input']).data('removeUrl');
            var formData = TU{!! $imageObj !!}.formData;
            formData['remove_id'] = id;

            $.post(removeUrl, TU{!! $imageObj !!}.formData, function(data){

                if(data['result']) {

                    TU{!! $imageObj !!}.removeBox($(self).parent().parent().parent().parent().parent());


                }
                {!! (!empty($callbacks['remove'])) ? $callbacks['remove'] : '' !!}

            }, 'json');

            return false;

        },
        removeBox: function(targetObj) {

            targetObj.remove();
            TU{!! $imageObj !!}.processingFile--;
            TU{!! $imageObj !!}.imageOrder--;
            TU{!! $imageObj !!}.formData['surpass_overwrite_id'] = -1;
            TU{!! $imageObj !!}.overwritePreviewBox = null;

            TU{!! $imageObj !!}.resizeheight();

            $('#preview_images').trigger('sortupdate');

        },
        overwrite: function(self, container, targetId) {

            $(self).addClass('disabled');

            TU{!! $imageObj !!}.formData['surpass_overwrite_id'] = targetId;
            TU{!! $imageObj !!}.overwritePreviewBox = $(container);
            $('#'+ TU{!! $imageObj !!}.ids['input']).click();
            TU{!! $imageObj !!}.imageOrder--;

            $(self).removeClass('disabled');

        },
        isFull: function() {

            var processingFileCount = TU{!! $imageObj !!}.processingFile;
            var maxFileCount = TU{!! $imageObj !!}.maxFile;

            if(TU{!! $imageObj !!}.formData['surpass_overwrite_id'] > 0
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
        <button{!! $css_changebutton !!} type="button" imageid="{%=o.surpassId%}" onclick="return TU{!! $imageObj !!}.overwrite(this, this.parentNode.parentNode.parentNode.parentNode, {%=o.surpassId%});">Verander</button>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <button{!! $css_editbutton !!} type="button" onclick="openResizeCropModal(this,{%=o.surpassId%},'{%=o.img%}')" >bewerk</button>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
        <button{!! $css_deletebutton !!} type="button" onclick="return TU{!! $imageObj !!}.remove(this, {%=o.surpassId%});">{!! $button_label !!}</button>
    </div>
    @endshield
</script>
<script>

    $(document).ready(function(){

        TU{!! $imageObj !!}.ids = {
            input: '{!! $input_id !!}',
            preview: '{!! $preview_id !!}'
        };
        TU{!! $imageObj !!}.maxFile = {!! $max_file !!};

        @if(!empty($load_data))

        TU{!! $imageObj !!}.loadData = {!! json_encode($load_data) !!};

        @endif

        @if(!empty($form_data))

        TU{!! $imageObj !!}.formData = {!! json_encode($form_data) !!};

        @endif

        @if(!empty($preview_params))

        TU{!! $imageObj !!}.previewParameters = {!! json_encode($preview_params) !!};

        @endif

        @if(!empty($progress))

        TU{!! $imageObj !!}.progress = '{!! $progress !!}';

        @endif

        TU{!! $imageObj !!}.overCallback = function(){
            alert('{!! $alert !!}');
        };
        TU{!! $imageObj !!}.init();

        $( window ).resize(function() {
            TU{!! $imageObj !!}.resizeheight();
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
            var input = $('#'+ TU{!! $imageObj !!}.ids['input']);
            var uploadUrl = $('#'+ TU{!! $imageObj !!}.ids['input']).attr('data-url');
            var fileName = "upload.png";

            var loadingBox = tmpl('loading_box_change_{!! $dir !!}', {content: TU{!! $imageObj !!}.progress, imageid: imgid});
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


                                    $('#'+ TU{!! $imageObj !!}.ids['preview'] +' .box-image:not(.used) > :first').parent().addClass("used").attr('id', data['insertId']);


                                    loadImage(blob, function (img) {

                                        if(img.type != 'error') {
                                            TU{!! $imageObj !!}.preview(
                                                    img,
                                                    data['insertId'],
                                                    data['filename'],
                                                    saveMode,
                                                    data['file_path'],
                                                    data['filename']
                                            );
                                            {!! (!empty($callbacks['done'])) ? $callbacks['done'] : '' !!}

                                        }

                                    }, TU{!! $imageObj !!}.previewParameters, data['file_path']);


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