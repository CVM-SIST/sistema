<!doctype html>
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Club Villa Mitre</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->
        <link href="http://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic" rel="stylesheet" type="text/css">
        <!-- needs images, font... therefore can not be part of ui.css -->
        <link rel="stylesheet" href="<?=$baseurl?>bower_components/font-awesome/css/font-awesome.min.css">

        <!-- end needs images -->

            <link rel="stylesheet" href="<?=$baseurl?>styles/ui.css"/>
            <link rel="stylesheet" href="<?=$baseurl?>styles/main.css">
            <link rel="stylesheet" href="<?=$baseurl?>styles/notifIt.css">
            <link rel="stylesheet" href="<?=$baseurl?>styles/jquery.fileupload.css">

            <?
            /*if($redirect){
            ?>
            <script type="text/javascript">document.location.href = '<?=$redirect?>'</script>
            <?
            }*/
            ?>
        <link rel="icon" href="<?=$baseurl?>images/favicon.png" type="image/x-icon" />
    </head>
    <body data-ng-app="app" id="app" data-custom-background="" data-off-canvas-nav="">
        <!--[if lt IE 9]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <div data-ng-controller="AppCtrl">
            <div data-ng-hide="isSpecificPage()" data-ng-cloak="">
                <section  id="header" class="top-header">
                    <header class="clearfix">
                        <a href="#/" data-toggle-min-nav
                                     class="toggle-min"
                                     ><i class="fa fa-bars"></i></a>

                        <!-- Logo -->
                        <div class="logo">
                            <a href="<?=$baseurl?>admin">
                                <span>Villa Mitre</span>
                            </a>
                        </div>

                        <!-- needs to be put after logo to make it working-->
                        <div class="menu-button" toggle-off-canvas>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </div>


                        <div class="top-nav">
			<div>
                    	  <ul class="nav-right pull-right">
				login: <?=$username?>
			  </ul>
			</div>
                    	  <ul class="nav-right pull-right list-unstyled">

                                <li class="dropdown text-normal nav-profile">
                                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                                        <img src="<?=$baseurl?>images/g1.jpg" alt="" class="img-circle img30_30">
                                        <span class="hidden-xs">
                                            <span data-i18n=""></span>
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu with-arrow pull-right">
					<? if ( $rango == 0 ) { ?>
                                        	<li>
                                            		<a href="<?=$baseurl?>admin/admins">
                                                	<i class="fa fa-user"></i>
                                                	<span data-i18n="Administradores"></span>
                                            		</a>
                                        	</li>
					<? } ?>
                                        <li>
                                            <a href="<?=$baseurl?>admin/admins/chgpwd">
                                                <i class="fa fa-key"></i>
                                                <span data-i18n="Cambiar Contraseña"></span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?=$baseurl?>admin/logout">
                                                <i class="fa fa-sign-out"></i>
                                                <span data-i18n="Cerrar Sesión"></span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                            </ul>
                        </div>

                    </header>
                </section>


                <aside data-ng-include=" '<?=$baseurl?>views/nav.php?baseurl=<?=$baseurl?>&section=<?=$section?>&rango=<?=$rango?>' " id="nav-container"></aside>
            </div>
                <section id="content" class="cvm_section">

                    <? include($section.".php"); ?>

                </section>

        </div>


        <script src="<?=$baseurl?>scripts/vendor.js" type="text/javascript" ></script>

        <script src="<?=$baseurl?>scripts/ui.js" type="text/javascript" ></script>

        <script src="<?=$baseurl?>scripts/jquery-ui-1.10.4.min.js" type="text/javascript" ></script>


        <script src="<?=$baseurl?>scripts/app.js" type="text/javascript" ></script>

        <script src="<?=$baseurl?>scripts/webcam.js" type="text/javascript" ></script>
        <script src="<?=$baseurl?>scripts/jquery.fileupload.js" type="text/javascript" ></script>

        <script src="<?=$baseurl?>scripts/jquery.autocomplete.js" type="text/javascript" ></script>
        <script src="<?=$baseurl?>scripts/bootstrap.min.js" type="text/javascript" ></script>
        <script src="<?=$baseurl?>scripts/jquery.ui.datepicker-es.js" type="text/javascript" ></script>
        <script src="<?=$baseurl?>scripts/tinymce/tinymce.min.js" type="text/javascript" ></script>


        <script>
        /*jslint unparam: true */
        /*global window, $ */
        $(function () {
            'use strict';
            // Change this to the location of your server-side upload handler:

            $('#fileupload').fileupload({
                url: '<?=base_url()?>admin/socios/subir_imagen',
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
			var fileExt = file.name.split('.').pop();
			if ( fileExt !== 'jpg' && fileExt !== 'JPG' ) {
				alert("Extensión invalida. El archivo debe ser JPG");
				return false;
			}
			if ( file.size > 100000 ) {
				alert("Tamaño demasiado grande("+file.size+"). Máximo 100k");
				return false;
			}
                        $("#my_result").html('<img src="<?=$baseurl?>images/temp/'+file.name+'" width="100%">');
                        $('#progress .progress-bar').hide();
                    });
                },
                progressall: function (e, data) {
                    $('#progress .progress-bar').show();
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                    );
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        });
        </script>


        <script>
        /*jslint unparam: true */
        /*global window, $ */
        $(function () {
            'use strict';
            // Change this to the location of your server-side upload handler:

            $('#fileupload_mail').fileupload({
                url: '<?=base_url()?>admin/envios/subir_imagen',
                dataType: 'json',
                done: function (e, data) {
                    $.each(data.result.files, function (index, file) {
                        $("#my_result").html('<img src="<?=$baseurl?>images/temp/'+file.name+'" width="100%">');
                        $('#progress .progress-bar').hide();
                    });
                },
                progressall: function (e, data) {
                    $('#progress .progress-bar').show();
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                    );
                }
            }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        });
        </script>

        <script type="text/javascript">
	function openWindowWithPost(url,name,keys,values)
	{
    		var newWindow = window.open(url, name,'menubar=yes,toolbar=yes,width=800,height=600');
    		if (!newWindow) return false;
	
    		var html = "";
    		html += "<html><head></head><body><form id='formid' method='post' action='" + url +"'>";

    		if (keys && values && (keys.length == values.length))
        	for (var i=0; i < keys.length; i++)
            		html += "<input type='hidden' name='" + keys[i] + "' value='" + values[i] + "'/>";
    			html += "</form><script type='text/javascript'>document.getElementById(\"formid\").submit()</sc"+"ript></body></html>";

    		newWindow.document.write(html);
    		return newWindow;
	}
	</script>


        <script type="text/javascript">

            <? if($section=='estadisticas-facturacion'){ ?>

            Morris.Line({
              element: 'facturacion-mensual',
              data: <?=$facturacion_mensual?>,
              xkey: 'y',
              ykeys: ['a','b'],
              labels: ['Facturación','Pagos']
            });

            Morris.Line({
              element: 'facturacion-anual',
              data: <?=$facturacion_anual?>,
              xkey: 'y',
              ykeys: ['a','b'],
              labels: ['Facturación','Pagos']
            });

            <? }else if($section=='estadisticas-actividades'){ ?>

            Morris.Bar({
                element: 'actividades-mensual',
                data: <?=$actividades_mensual['data']?>,
                xkey: 'y',
                ykeys: <?=$actividades_mensual['keys']?>,
                labels: <?=$actividades_mensual['labels']?>,
                stacked: true
            });

            Morris.Bar({
                element: 'actividades-anual',
                data: <?=$actividades_anual['data']?>,
                xkey: 'y',
                ykeys: <?=$actividades_anual['keys']?>,
                labels: <?=$actividades_anual['labels']?>,
                stacked: true
            });
            <? } ?>
            function check_eliminar_rifa(){
                var agree = confirm("Seguro que desea eliminar esta Rifa?");
                if(agree){return true}else{return false}
            }
            function check_eliminar_cat(){
                var agree = confirm("Seguro que desea eliminar esta Categoria?");
                if(agree){return true}else{return false}
            }
            function check_eliminar_act(){
                var agree = confirm("Seguro que desea eliminar esta Actividad?");
                if(agree){return true}else{return false}
            }
            function check_eliminar_socio(){
                var agree = confirm("Seguro que desea eliminar este Socio?");
                if(agree){return true}else{return false}
            }
            $(document).ready(function()
            {
                $("div#socio_desc").each(function(){
                    var id = $(this).data('id');
                    if($(this).height() >= 24){
                        $(this).addClass('socios_desc');
                    }else{
                        $("a#ver_mas[data-id="+id+"]").addClass("hidden");
                    }
                })
                $("a#ver_mas").click(function(){
                    var id = $(this).data('id');
                    var toggle = $(this).data('toggle');
                    if(toggle == '0'){
                        $("div[data-id="+id+"]").removeClass('socios_desc');
                        $(this).data('toggle','1');
                        $(this).text('Ver Menos');
                    }else{
                        $("div[data-id="+id+"]").addClass('socios_desc');
                        $(this).data('toggle','0');
                        $(this).text('Ver Más');
                    }
                })
                $("a#btn-eliminar-socio").click(function(){
                    var agree = confirm("Seguro que desea eliminar este Socio?");
                    if(agree){return true}else{return false}
                })
                $("a#btn-eliminar-profesor").click(function(){
                    var agree = confirm("Seguro que desea eliminar esta Comisión y desvincular todas sus actividades?");
                    if(agree){return true}else{return false}
                })
                $("a#btn-eliminar-lugar").click(function(){
                    var agree = confirm("Seguro que desea eliminar este Lugar?");
                    if(agree){return true}else{return false}
                })
                $("a#btn-eliminar-actividad").click(function(){
                    var agree = confirm("Seguro que desea eliminar esta Actividad?");
                    if(agree){return true}else{return false}
                })



                $('a#cliente_info_toogle').click(function(){
                   // $(this).parent().parent().next().toggle();
                    if($(this).hasClass("fa-plus-square-o")){
                        $(this).removeClass();
                        $(this).addClass("fa-minus-square-o");
                    }else{
                        $(this).removeClass();
                        $(this).addClass("fa-plus-square-o");
                    }
                });

                $('#btn-meses').click(function(){
                    $('#morosos-opt').hide(function(){
                    $('#morosos-meses').show();
                });



            })

                $('#btn-act').click(function(){
                    $('#morosos-opt').hide(function(){

                    $('#morosos-act').show();
                    });

                })

                $('button#morosos-cancel').click(function(){
                    $('#morosos-meses').hide();
                    $('#morosos-act').hide(function(){

                    $('#morosos-opt').show();
                    });

                })

                $("button#morosos-ver").click(function(){
                    $('#table-morosos').show();
                })

		$("#btn_profesor").click(function() {
                    var sid = $("#sid-select").val();
            		$.get("<?=$baseurl?>admin/socios/get/"+sid,function(data){
                	if(data != 'false' ){
                    		var socio = $.parseJSON(data);
				var ok = confirm("El socio "+sid+" se llama "+socio.apellido+", "+socio.nombre+ " Confirma?");
				if (ok) {
					return true;
				} else {
					return false;
				}
			} else {
				alert("El socio "+sid+" No existe");
				return false;
			}

			})

		})

		$("#save_btn").click(function() {
		    var categ = $("#s_cate").val();
		    // No controlo para categoria sponsor
		    if ( categ != 12 ) {
                    	var fecha = $("#fechan").val();
			<?  $hoy=date('Y-m-d'); ?>
		    	if ( fecha == 0 || fecha == "0000-00-00" ) {
				alert ("Error en la fecha de nacimiento, no puede ser 0");
				return false;
		   	}
                    	if ( fecha > '<?=$hoy?>' ) {
                        	alert ("Error en la fecha de nacimiento, no puede ser mayor a hoy");
                        	return false;
                   	}
		  };
		})

                <?
                if($section == 'socios-editar'){
                ?>
                    var fecha = $("#fechan").val();
                    var res = fecha.split("-");
                    res[1]--;
                    var d = new Date(res[2],res[1],res[0]);
                    var n = d.getTime();
                    if($.now()-d > 567648000000){
                        $("#menor").hide();
                    }else{
                        $("#menor").show();
                    }
                <?
                }
                ?>

                $( "#domicilio" ).focus(function(){
                    var fecha = $("#fechan").val();
                    var res = fecha.split("-");
                    res[1]--;
                    var d = new Date(res[0],res[1],res[2]);
                    var n = d.getTime();
                    if($.now()-d > 567648000000){
                        $("#menor").hide();
                        $("#s_cate").val("2");
                    }else{
                        $("#menor").show();
                        $("#s_cate").val("1");
                    }
                })

                $('#conf-tab a').click(function (e) {
                  e.preventDefault()
                  $(this).tab('show')
                })
            })
        </script>

        <? if($section == 'socios-nuevo' || ($section == 'socios-editar' && $socio) ){ ?>
        <script language="JavaScript">
        Webcam.setSWFLocation("<?=$baseurl?>scripts/webcam.swf");
        Webcam.attach( '#my_camera' );

        function take_snapshot() {
            $("#save_btn").attr("disabled", "disabled");
            $("#save_btn").html("Guardando imagen...");
            var data_uri = Webcam.snap();
            document.getElementById('my_result').innerHTML = '<img src="'+data_uri+'"/>';
            Webcam.upload( data_uri, '<?=$baseurl?>admin/socios/agregar_imagen', function(code, text) {
                $("#save_btn").removeAttr("disabled");
                $("#save_btn").html("Guardar");
                // 'code' will be the HTTP response code from the server, e.g. 200
                // 'text' will be the raw response content
            } );
        }



        a= $('#nombre').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-nombre' });
        a= $('#apellido').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-apellido'  });
        a= $('#localidad').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-localidad' });
        a= $('#nacionalidad').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-nacionalidad' });
        a= $('#r1').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
        onSelect: function (suggestion) {
                $('#r1').val(suggestion.data);
            } });
        a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
        onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
            }  });
        a= $('#r3').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-apellido|nombre|dni',
            onSelect: function (suggestion) {
                $('#r3').val(suggestion.data);
            }
        });

        $("a#r-buscar").click(function(){
            var id = $(this).data('id');
            $("#"+id+"-loading").removeClass('hidden');
            var dni = $("#"+id).val();
            $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                $("#"+id+"-loading").addClass('hidden');
                if(data){
                    var socio = $.parseJSON(data);
                    var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                    $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                    $("#"+id+"-data").addClass('hidden');
                    $("#"+id+"-result").removeClass('hidden');
                    $("#tutor_sid").val(socio[0].Id);
                    $("#"+id+"-id").val(socio[0].Id);
                }else{
                   angular.element("#modal_open").triggerHandler('click');
                   $("#tutor-dni").val($("#"+id).val());
                   $("#tutor-nombre").focus();
                   $("#form-tutor").data("id",id);
                }
            })
        })
        function submit_tutor(){

            var tutor = $( "#form-tutor" ).serialize();
            $.get("<?=$baseurl?>admin/socios/nuevo-tutor?"+tutor, function(data){
                if(data == 'DNI'){
                    alert("El DNI Ingresado ya existe o no es valido.")
                }else{
                    angular.element("#modal_close").triggerHandler('click');
                    var socio = $.parseJSON(data);
                    var id = $("#form-tutor").data('id');
                    var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                    $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio.nombre+' '+socio.apellido+' ('+socio.dni+') '+close_link);
                    $("#"+id+"-data").addClass("hidden");
                    $("#"+id+"-result").removeClass("hidden");
                    $("#"+id+"-id").val(socio.Id);
                }
            })
        }
        function cleear(id){
            $("#"+id+"-data").removeClass('hidden');
            $("#tutor_sid").val(0);
            $("#tutor_dni").val(0);
            $("#"+id+"-result").addClass('hidden');
        }
        </script>
        <? } ?>
        <? if($section == 'actividades-asociar'){ ?>
        <script type="text/javascript">
        $(document).ready(function(){
            a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
                onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
                }
            });

        })
        a= $('#activ').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-nombre' });


        $("#activ_asoc_form").submit(function(){
            var id = 'r2';
            $("#"+id+"-loading").removeClass('hidden');
            var dni = $("#"+id).val();
            $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                $("#"+id+"-loading").addClass('hidden');
                if(data){
                    var socio = $.parseJSON(data);
                    var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                    $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                    $("#"+id+"-data").addClass('hidden');
                    $("#"+id+"-result").removeClass('hidden');

                    $("#acceso_editar").attr('href','<?=$baseurl?>admin/socios/editar/'+socio[0].Id);
                    $("#acceso_cupon").attr('href','<?=$baseurl?>admin/pagos/cupon/'+socio[0].Id);
                    $("#acceso_ver_resumen").attr('href','<?=$baseurl?>admin/socios/resumen/'+socio[0].Id);
                    $("#acceso_pago").attr('href','<?=$baseurl?>admin/pagos/registrar/'+socio[0].Id);
                    $("#acceso_deuda").attr('href','<?=$baseurl?>admin/pagos/deuda/'+socio[0].Id);
                    $("#acceso_resumen").attr('href','<?=$baseurl?>admin/socios/enviar_resumen/'+socio[0].Id);
                    $("#accesos_directos").removeClass('hidden');


                    $("#"+id+"-id").val(socio[0].Id);
                    get_actividades(socio[0].Id);
                }else{
                   alert("El DNI ingresado no se encuentra en la Base de Datos.")
                }
            })
        })
        function get_actividades(id){
            $("#"+id+"-loading").removeClass('hidden');
            $.get( "<?=$baseurl?>admin/actividades/get/"+id ).done(function(data){
                $("#asociar-div").html(data);
                $("#asociar-div").slideDown();
                $("#"+id+"-loading").addClass('hidden');
            })

        }
        function cleear(id){
            $("#"+id+"-data").removeClass('hidden');
            $("#"+id+"-id").val('0');
            $("#"+id+"-result").addClass('hidden');
            $("#asociar-div").slideUp();
            $("#accesos_directos").addClass('hidden');
        }



        <? if($socio->Id && $socio->Id != 0){ ?> $("#asociar-div").slideDown(); get_actividades("<?=$socio->Id?>"); <? } ?>
        </script>
        <? } ?>
        <? if($section == 'pagos-cupon'){ ?>
        <script type="text/javascript">
        $(document).ready(function(){
            a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
                onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
                }
            });
        })
        a= $('#activ').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-nombre' });
        $("#gen_cupon_form").submit(function(){
            var id = 'r2';
            $("#"+id+"-loading").removeClass('hidden');
            var dni = $("#"+id).val();
            $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                $("#"+id+"-loading").addClass('hidden');
                if(data){
                    var socio = $.parseJSON(data);
                    var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                    $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                    $("#"+id+"-data").addClass('hidden');
                    $("#"+id+"-result").removeClass('hidden');
                    $("#"+id+"-id").val(socio[0].Id);

                    $("#acceso_editar").attr('href','<?=$baseurl?>admin/socios/editar/'+socio[0].Id);
                    $("#acceso_actividad").attr('href','<?=$baseurl?>admin/actividades/asociar/'+socio[0].Id);
                    $("#acceso_ver_resumen").attr('href','<?=$baseurl?>admin/socios/resumen/'+socio[0].Id);
                    $("#acceso_pago").attr('href','<?=$baseurl?>admin/pagos/registrar/'+socio[0].Id);
                    $("#acceso_deuda").attr('href','<?=$baseurl?>admin/pagos/deuda/'+socio[0].Id);
                    $("#acceso_resumen").attr('href','<?=$baseurl?>admin/socios/enviar_resumen/'+socio[0].Id);
                    $("#accesos_directos").removeClass('hidden');

                    get_cupon(socio[0].Id);
                }else{
                    alert("El DNI ingresado no se encuentra en la Base de Datos.")
                }
            })
        })
        function get_cupon(id){
             $("#cupon-div").html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
            $.get( "<?=$baseurl?>admin/pagos/cupon/get/"+id ).done(function(data){
                $("#cupon-div").html(data);
                $("#cupon-div").slideDown();

            })

        }

        <? if($socio->Id && $socio->Id != 0){ ?> $("#cupon-div").slideDown(); get_cupon("<?=$socio->Id?>"); <? } ?>
        function cleear(id){
            $("#"+id+"-data").removeClass('hidden');
            $("#"+id+"-id").val('0');
            $("#"+id+"-result").addClass('hidden');
            $("#cupon-div").slideUp();
             $("#accesos_directos").addClass('hidden');
        }
        </script>
        <? } ?>
        <? if($section == 'pagos-registrar'){ ?>

        <script language="JavaScript">

        $(document).ready(function(){
            a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
                onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
                }
            });

            $("#pagos_reg_form").submit(function(){
                var id = 'r2';
                $("#"+id+"-loading").removeClass('hidden');
                var dni = $("#"+id).val();
                $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                    $("#"+id+"-loading").addClass('hidden');
                    if(data){
                        var socio = $.parseJSON(data);
                        var close_link = '<a href="#" onclick="cliar(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                        $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                        $("#"+id+"-data").addClass('hidden');
                        $("#"+id+"-result").removeClass('hidden');
                        $("#"+id+"-id").val(socio[0].Id);

                        $("#acceso_editar").attr('href','<?=$baseurl?>admin/socios/editar/'+socio[0].Id);
                        $("#acceso_actividad").attr('href','<?=$baseurl?>admin/actividades/asociar/'+socio[0].Id);
                        $("#acceso_ver_resumen").attr('href','<?=$baseurl?>admin/socios/resumen/'+socio[0].Id);
                        $("#acceso_cupon").attr('href','<?=$baseurl?>admin/pagos/cupon/'+socio[0].Id);
                        $("#acceso_deuda").attr('href','<?=$baseurl?>admin/pagos/deuda/'+socio[0].Id);
                        $("#acceso_resumen").attr('href','<?=$baseurl?>admin/socios/enviar_resumen/'+socio[0].Id);
                        $("#accesos_directos").removeClass('hidden');

                        get_pago(socio[0].Id);
                    }else{
                        alert("El DNI ingresado no se encuentra en la Base de Datos.")
                    }
                })
            })
            function get_pago(id){
                 $("#pago-div").html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                 $.get( "<?=$baseurl?>admin/pagos/registrar/get/"+id ).done(function(data){
                    $("#pago-div").html(data);
                    $("#pago-div").slideDown();

                })

            }



            <? if($socio->Id && $socio->Id != 0){ ?> $("#pago-div").slideDown(); get_pago("<?=$socio->Id?>"); <? } ?>


        })

        function cliar(id){
                $("#"+id+"-data").removeClass('hidden');
                $("#"+id+"-id").val('0');
                $("#"+id+"-result").addClass('hidden');
                $("#pago-div").slideUp();
                 $("#accesos_directos").addClass('hidden');
            }

            $("#cuotas").keyup(function(){
                if($("#cuotas").val() && $("#monto").val()){
                    if($.isNumeric($("#cuotas").val()) && $.isNumeric($("#monto").val())){
                        if($("#cuotas").val() <= 0){
                            alert("Ingrese un numero mayor que 0");
                            return false;
                        }
                            var valor_cuota = $("#monto").val()/$("#cuotas").val();

                                $("#valor-cuota").val(valor_cuota);
                    }else{
                        alert("Por Favor Ingrese solo Números en los campos Monto y Cantidad de Cuotas");
                    }
                }
            })
         </script>
        <? } ?>

        <? if($section == 'pagos-deuda'){ ?>

        <script language="JavaScript">

        $(document).ready(function(){
            a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
                onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
                }
            });

            $("#pagos_deuda_form").submit(function(){
                var id = 'r2';
                $("#"+id+"-loading").removeClass('hidden');
                var dni = $("#"+id).val();
                $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                    $("#"+id+"-loading").addClass('hidden');
                    if(data){
                        var socio = $.parseJSON(data);
                        var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                        $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                        $("#"+id+"-data").addClass('hidden');
                        $("#"+id+"-result").removeClass('hidden');
                        $("#"+id+"-id").val(socio[0].Id);

                        $("#acceso_editar").attr('href','<?=$baseurl?>admin/socios/editar/'+socio[0].Id);
                        $("#acceso_actividad").attr('href','<?=$baseurl?>admin/actividades/asociar/'+socio[0].Id);
                        $("#acceso_ver_resumen").attr('href','<?=$baseurl?>admin/socios/resumen/'+socio[0].Id);
                        $("#acceso_cupon").attr('href','<?=$baseurl?>admin/pagos/cupon/'+socio[0].Id);
                        $("#acceso_pago").attr('href','<?=$baseurl?>admin/pagos/registrar/'+socio[0].Id);
                        $("#acceso_resumen").attr('href','<?=$baseurl?>admin/socios/enviar_resumen/'+socio[0].Id);
                        $("#accesos_directos").removeClass('hidden');

                        get_pago(socio[0].Id);
                    }else{
                        alert("El DNI ingresado no se encuentra en la Base de Datos.")
                    }
                })
            })
            function get_pago(id){
                 $("#deuda-div").html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                 $.get( "<?=$baseurl?>admin/pagos/deuda/get/"+id ).done(function(data){
                    $("#deuda-div").html(data);
                    $("#deuda-div").slideDown();

                })

            }



            <? if($socio->Id && $socio->Id != 0){ ?> $("#pago-div").slideDown(); get_pago("<?=$socio->Id?>"); <? } ?>


        })

        function cleear(id){
                $("#"+id+"-data").removeClass('hidden');
                $("#"+id+"-id").val('0');
                $("#"+id+"-result").addClass('hidden');
                $("#deuda-div").slideUp();
                 $("#accesos_directos").addClass('hidden');
            }


         </script>
        <? } ?>

        <? if($section == 'pagos-facturacion'){ ?>
        <script type="text/javascript" src="<?=$baseurl?>scripts/notifIt.js"></script>
            <script type="text/javascript">
                $("#generar").click(function(){
                    notif({
                        msg: "<b>Alerta :</b> Recuerde ingresar en la opción <a href='<?=$baseurl?>admin/pagos/facturacion'>Pagos -> Facturacion Mensual</a> para generar la facturación del mes actual.",
                        type: "warning",
                        width: "all",
                        opacity: "0.8",
                        color: "#FFF",
                        autohide: false
                    });
                })
            </script>
        <? } ?>
        <? if($section == 'socios-resumen'){ ?>
        <script type="text/javascript">
        $(document).ready(function(){
            a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
                onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
                }
            });

        })
        a= $('#activ').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/search/socios-nombre' });
        $("#buscar_resumen").submit(function(e){
        //$("a#r-buscar").click(function(){
            var id = 'r2';
            $("#"+id+"-loading").removeClass('hidden');
            var dni = $("#"+id).val();
            $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                $("#"+id+"-loading").addClass('hidden');
                if(data){
                    var socio = $.parseJSON(data);
                    var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                    $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                    $("#"+id+"-data").addClass('hidden');
                    $("#"+id+"-result").removeClass('hidden');

                    $("#acceso_editar").attr('href','<?=$baseurl?>admin/socios/editar/'+socio[0].Id);
                    $("#acceso_cupon").attr('href','<?=$baseurl?>admin/pagos/cupon/'+socio[0].Id);
                    $("#acceso_actividad").attr('href','<?=$baseurl?>admin/actividades/asociar/'+socio[0].Id);
                    $("#acceso_pago").attr('href','<?=$baseurl?>admin/pagos/registrar/'+socio[0].Id);
                    $("#acceso_deuda").attr('href','<?=$baseurl?>admin/pagos/deuda/'+socio[0].Id);
                    $("#acceso_resumen").attr('href','<?=$baseurl?>admin/socios/enviar_resumen/'+socio[0].Id);
                    $("#accesos_directos").removeClass('hidden');

                    $("#"+id+"-id").val(socio[0].Id);
                    get_actividades(socio[0].Id);
                }else{
                   alert("El DNI ingresado no se encuentra en la Base de Datos.")
                }
            })
            e.preventDefault();
            return false;
        })

        function get_actividades(id){
            $("#"+id+"-loading").removeClass('hidden');
            $.get( "<?=$baseurl?>admin/socios/resumen2/"+id ).done(function(data){
                $("#asociar-div").html(data);
                $("#asociar-div").slideDown();
                $("#"+id+"-loading").addClass('hidden');
            })

        }
        function cleear(id){
            $("#"+id+"-data").removeClass('hidden');
            $("#"+id+"-id").val('0');
            $("#"+id+"-result").addClass('hidden');
            $("#asociar-div").slideUp();
            $("#accesos_directos").addClass('hidden');
        }



        <? if($socio->Id && $socio->Id != 0){ ?> $("#asociar-div").slideDown(); get_actividades("<?=$socio->Id?>"); <? } ?>
        </script>
        <? } ?>
<!--

-->

        <? if($section == 'pagos-editar'){ ?>

        <script language="JavaScript">

        $(document).ready(function(){
            a= $('#r2').autocomplete({ serviceUrl:'<?=$baseurl?>autocomplete/get/socios-dni|nombre|apellido',
                onSelect: function (suggestion) {
                $('#r2').val(suggestion.data);
                }
            });

            $("#pagos_deuda_form").submit(function(){
                var id = 'r2';
                $("#"+id+"-loading").removeClass('hidden');
                var dni = $("#"+id).val();
                $.get("<?=$baseurl?>autocomplete/buscar_socio/dni/"+dni,function(data){
                    $("#"+id+"-loading").addClass('hidden');
                    if(data){
                        var socio = $.parseJSON(data);
                        var close_link = '<a href="#" onclick="cleear(\''+id+'\')" title="Quitar" style="color:#F00"><i class="fa fa-times" ></i></a>'
                        $("#"+id+"-result").html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+socio[0].nombre+' '+socio[0].apellido+' ('+socio[0].dni+') '+close_link);
                        $("#"+id+"-data").addClass('hidden');
                        $("#"+id+"-result").removeClass('hidden');
                        $("#"+id+"-id").val(socio[0].Id);

                        $("#acceso_editar").attr('href','<?=$baseurl?>admin/socios/editar/'+socio[0].Id);
                        $("#acceso_actividad").attr('href','<?=$baseurl?>admin/actividades/asociar/'+socio[0].Id);
                        $("#acceso_ver_resumen").attr('href','<?=$baseurl?>admin/socios/resumen/'+socio[0].Id);
                        $("#acceso_cupon").attr('href','<?=$baseurl?>admin/pagos/cupon/'+socio[0].Id);
                        $("#acceso_pago").attr('href','<?=$baseurl?>admin/pagos/registrar/'+socio[0].Id);
                        $("#acceso_resumen").attr('href','<?=$baseurl?>admin/socios/enviar_resumen/'+socio[0].Id);
                        $("#accesos_directos").removeClass('hidden');

                        get_pago(socio[0].Id);
                    }else{
                        alert("El DNI ingresado no se encuentra en la Base de Datos.")
                    }
                })
            })
            function get_pago(id){
                 $("#deuda-div").html('<i class="fa fa-spinner fa-spin"></i> Cargando...');
                 $.get( "<?=$baseurl?>admin/pagos/get_pagos/"+id ).done(function(data){
                    $("#deuda-div").html(data);
                    $("#deuda-div").slideDown();

                })

            }



            <? if($socio->Id && $socio->Id != 0){ ?> $("#pago-div").slideDown(); get_pago("<?=$socio->Id?>"); <? } ?>


        })

        function cleear(id){
                $("#"+id+"-data").removeClass('hidden');
                $("#"+id+"-id").val('0');
                $("#"+id+"-result").addClass('hidden');
                $("#deuda-div").slideUp();
                 $("#accesos_directos").addClass('hidden');
            }


         </script>
        <? } ?>

<script type="text/javascript">
function fecha_db_mostrar($fecha) {
        $anio = $fecha.substring(0,4);
        $mes = $fecha.substring(5,7);
        $dia = $fecha.substring(8,10);

	return $dia.concat("-",$mes,"-",$anio);
}

function fecha_mostrar_db($fecha) {
        $anio = $fecha.substring(6,10);
        $mes = $fecha.substring(3,5);
        $dia = $fecha.substring(0,2);

	return $anio.concat("-",$mes,"-",$dia);
}

$("#load-asoc-activ-form select").on('change',function(){
        switch ( $(this).val() ) {
                case 'txt':
                        $("#archivo-form").removeClass('hidden');
                        break;
                case 'bd':
                        $("#archivo-form").addClass('hidden');
                        break;
        }
})

$("#busqueda_socio").submit(function(){
	var apellido = $("#apellido").val();
	var nombre = $("#nombre").val();
	var domicilio = $("#domicilio").val();
	var email = $("#email").val();
	var categoria = $("#categoria").val();
	var tutor = $("#tutor").val();

	if ( apellido == "" && nombre == "" && domicilio == "" && email == "" && categoria == "" && tutor == "" ) {
                alert("Debe cargar algun filtro");
                return false;
        }
})

$("#asoc-act-filtro-form").submit(function(){
        var actividad = $("#actividad").val();
        var email = $("#email").val();
	if ( email == "" ) {
		alert("Debe elegir email");
		return false;
	}
        var telefono = $("#telefono").val();
	if ( telefono == "" ) {
		alert("Debe elegir telefono");
		return false;
	}
        var url = "<?=$baseurl?>admin/socios/act-datos-ver" + "/" + actividad + "/" + email + "/" + telefono;

        $("#asoc-act-filtro-form").attr("action",url);

        $("#asoc-act-filtro-form").submit();

        return true;

})

$("#load-asoc-activ-form").submit(function(){
        var actividad = $("#actividad").val();
        var fuente = $("#fuente").val();
	if ( fuente == "" ) {
		alert("Debe elegir fuente");
		return false;
	}
	if ( fuente == "bd" ) {
        	var url = "<?=$baseurl?>admin/actividades/subearchivo" + "/" + actividad + "/" + fuente;
	} else {
        	var archivo = $("#userfile").val();
		if ( !archivo ) {
			alert("Debe elegir archivo");
			return false;
		}
        	var dato1col = $("#dato1col").val();
		if ( dato1col == "" ) {
			alert ("Debe seleccionar clave de 1 columna del archivo");
			return false;
		}
        	var url = "<?=$baseurl?>admin/actividades/subearchivo" + "/" + actividad + "/" + fuente + "/" + dato1col;
	}


        $("#load-asoc-activ-form").attr("action",url);

        $("#load-asoc-activ-form").submit();

	return true;

})
$("#estad-activ-form").submit(function(){
        var actividad = $("#actividad").val();
        var url = "<?=$baseurl?>admin/estadisticas/cobranza_act" + "/" + actividad;

        $("#estad-activ-form").attr("action",url);
        $("#estad-activ-form").submit();

	return true;
})
$("#estad-comi-form").submit(function(){
        var comision = $("#comision").val();
        var url = "<?=$baseurl?>admin/estadisticas/cobranza_comi" + "/" + comision;

        $("#estad-comi-form").attr("action",url);
        $("#estad-comi-form").submit();

        return true;
})
$("button#estad_ing_excel").click(function(){
        $("#arma_excel").val('1');
        var mes = $("#meses").val();
        var url = "<?=$baseurl?>admin/estadisticas/ingresos" + "/" + mes;

        $("#estad-ing-form").attr("action",url);
        $("#estad-ing-form").submit();
        return true;
})

$("button#estad_act_excel").click(function(){
        $("#arma_excel").val('1');
        var actividad = $("#actividad").val();
        var url = "<?=$baseurl?>admin/estadisticas/cobranza_act" + "/" + actividad;

        $("#estad-activ-form").attr("action",url);
        $("#estad-activ-form").submit();
        return true;
})

var carnets ;
var carnet_visible = 0 ;
var carnets_total = 0 ;

$("#carnet_print").click(function(){

        var tipo_carnet = $("#carnet").val();
	var keys = new Array("tipo_carnet", "sid") 
	var values = new Array(tipo_carnet,carnets[carnet_visible].sid) 
	if ( tipo_carnet == 5 ) { 
                openWindowWithPost('<?=base_url()?>imprimir/carnet_plastico','',keys,values);
	} else {
                openWindowWithPost('<?=base_url()?>imprimir/carnets','',keys,values);
	}
	prox = carnet_visible + 1;
        if ( carnet_visible < carnets_total ) {
                $("#nxm").html('Carnet '+prox+' de '+carnets_total+' carnets a imprimir');
                $("#carnet_data").html("<div class='nap' > "+carnets[prox].Id +"</div> <div class='nap' >"+ carnets[prox].nombre +"</div> <div class='nap' >"+ carnets[prox].apellido +" </div> <div class='nap' >"+carnets[prox].dni+"</div>");
                carnet_visible =  prox;
        }

        return true;
})

$("#carnet_print_n").click(function(){

        var tipo_carnet = $("#carnet").val();
	var keys = new Array("tipo_carnet", "sid") 
	var sids = new Array();
	for ( i=0; i<10; i++ ) {
		sids[i] = carnets[carnet_visible].sid ;
		carnet_visible = carnet_visible + 1;
	}
	var values = new Array(tipo_carnet,sids) 
        if ( tipo_carnet == 5 ) {
                openWindowWithPost('<?=base_url()?>imprimir/carnet_plastico_n','',keys,values);
        } else {
                openWindowWithPost('<?=base_url()?>imprimir/carnets_n','',keys,values);
        }
        if ( carnet_visible < carnets_total ) {
                $("#nxm").html('Carnet '+carnet_visible+' de '+carnets_total+' carnets a imprimir');
                $("#carnet_data").html("<div class='nap' > "+carnets[carnet_visible].Id +"</div> <div class='nap' >"+ carnets[carnet_visible].nombre +"</div> <div class='nap' >"+ carnets[carnet_visible].apellido +" </div> <div class='nap' >"+carnets[carnet_visible].dni+"</div>");
        }

        return true;
})


$("#carnet_print_fte").click(function(){
        var tipo_carnet = $("#carnet").val();
	var keys = new Array("tipo_carnet") 
	var values = new Array(tipo_carnet) 
        openWindowWithPost('<?=base_url()?>imprimir/carnet_plastico_frente','',keys,values);
        return true;
})

$("#carnet_prox").click(function(){
        var prox = carnet_visible + 1;
	if ( carnet_visible < carnets_total ) {
		$("#nxm").html('Carnet '+prox+' de '+carnets_total+' carnets a imprimir');
		$("#carnet_data").html("<div class='nap' > "+carnets[prox].Id +"</div> <div class='nap' >"+ carnets[prox].nombre +"</div> <div class='nap' >"+ carnets[prox].apellido +" </div> <div class='nap' >"+carnets[prox].dni+"</div>");
		carnet_visible =  prox;
	}
})

$("button#btn_carnet_buscar").click(function(){
        var comision = $("#comision").val();
        var foto = $("#foto").val();
        var categoria = $("#categoria").val();
        var impresion = $("#impresion").val();
        var tipo_carnet = $("#carnet").val();

	if ( tipo_carnet == 1 ) { fte='<?=$baseurl?>/images/carnet-frente-new.png';  }
	if ( tipo_carnet == 2 ) { fte='<?=$baseurl?>/images/Prensa_Dorso_300.jpg';  }
	if ( tipo_carnet == 3 ) { fte='<?=$baseurl?>/images/Comercio_Dorso.jpg';  }
	if ( tipo_carnet == 4 ) { fte='<?=$baseurl?>/images/VMRacing_Dorso.jpg';  }
	if ( tipo_carnet == 5 ) { fte='<?=$baseurl?>/images/Plastico_2021_Dorso.jpg'; }
	if ( tipo_carnet == 6 ) { fte='<?=$baseurl?>/images/Mutual_14ago_Dorso.jpg'; }
	dor='<?=$baseurl?>/images/Plastico_2021_Frente.jpg';

      	$('#ver_plastico').css('display', 'block');
      	$('#plas_frente').css('background-image', 'url('+fte+')');
	$('#plas_dorso').css('background-image', 'url('+dor+')');

	carnet_visible = 0 ;
	$.get("<?=$baseurl?>admin/socios/carnets-buscar/"+categoria+"/"+foto+"/"+comision+"/"+impresion,function(data){
		if ( data ) {
			var js_carnets = $.parseJSON(data);
                        carnets = Object.values(js_carnets);
                        carnets_total = carnets.length;
			$("#nxm").html('Carnet 1 de '+carnets_total+' carnets a imprimir');
			$("#carnet_data").html("<div class='nap' > "+carnets[carnet_visible].Id +"</div> <div class='nap' >"+ carnets[carnet_visible].nombre +"</div> <div class='nap' >"+ carnets[carnet_visible].apellido +" </div> <div class='nap' >"+carnets[carnet_visible].dni+"</div>" );
			return true;
		}
	})

});
$("button#btn_print_hoja").click(function(){
        var hoja = $("input[name='hojas']:checked", "#carnets_hojas").val();
        var com = $("#com_sel").val();
        var foto = $("#foto_sel").val();
        var cat = $("#cat_sel").val();
        var carnet = $("#carnet_sel").val();
        if ( !hoja ) {
                alert("Debe Seleccionar una Hoja");
                return false;
        }
        window.open('<?=base_url()?>imprimir/carnets/'+hoja+'/'+com+'/'+foto+'/'+cat+'/'+carnet,'','menubar=yes,toolbar=yes,width=800,height=600');

        return true;
})

$("button#estad_comi_excel").click(function(){
        $("#arma_excel").val('1');
        var comision = $("#comision").val();
        var url = "<?=$baseurl?>admin/estadisticas/cobranza_comi" + "/" + comision;

        $("#estad-comi-form").attr("action",url);
        $("#estad-comi-form").submit();
        return true;
})

$("#comi-activ-form").submit(function(){
        var actividad = $("#actividad").val();
        var url = "<?=$baseurl?>comisiones/facturacion" + "/" + actividad;

        $("#comi-activ-form").attr("action",url);
        $("#comi-activ-form").submit();

        return true;
})



</script>


        <script type="text/javascript">
        <? /**




        **/ ?>


        $(document).on('click','#eliminar_pago',function(){
            var agree = confirm("Seguro que desea eliminar este pago?");
            if(!agree){return false;}
        })

        $("#filtro_morosos").click(function(){
            console.log('asd');
            var meses = $("#morosos_meses").val();
            var activ = $("#morosos_activ").val();
            document.location.href='<?=base_url()?>admin/pagos/'+meses+'/'+activ;
        })

        $(document).on("click","#valor_cuota",function(){
            $("#detalle_de_cuota").append('<i class="fa fa-spin fa-spinner"></i> Cargando...');
            angular.element("#detalle_de_cuota").append('click');
            angular.element("#modal_open").triggerHandler('click');
        })

        $("#imprimir_listado_morosos").click(function(){
            var meses = $(this).data('meses');
            var act = $(this).data('act');
            window.open('<?=base_url()?>imprimir/morosos/'+meses+'/'+act,'','width=800,height=600');
        })

        $(document).on("click","#imprimir_listado_categorias",function(){
            var cat = $(this).data('act');
            window.open('<?=base_url()?>imprimir/listado/categorias/'+act,'','width=800,height=600');
        })

        $(document).on("click","#imprimir_listado_actividades",function(){
            var act = $(this).data('act');
            window.open('<?=base_url()?>imprimir/listado/actividades/'+act,'','width=800,height=600');
        })

        $(document).on("click","#imprimir_carnet",function(){
        	var sid = $(this).data('id');
        	var tipo_carnet = 1;
        	var keys = new Array("tipo_carnet", "sid");
        	var values = new Array(tipo_carnet,sid);
        	openWindowWithPost('<?=base_url()?>imprimir/carnets','',keys,values);
        })

        $(document).on("click","#imprimir_tarjeta",function(){
        	var sid = $(this).data('id');
        	var tipo_carnet = 5;
        	var keys = new Array("tipo_carnet", "sid");
        	var values = new Array(tipo_carnet,sid);

		$.get("<?=$baseurl?>admin/socios/tiene-foto/"+sid,function(data){
                	if ( data == 'true' ) {
				openWindowWithPost('<?=base_url()?>imprimir/carnet_plastico','',keys,values);
                        	return true;
                	} else {
				var agree = confirm("No tiene foto imprime igual?");
				if(!agree){return false;}
				openWindowWithPost('<?=base_url()?>imprimir/carnet_plastico','',keys,values);
                        	return true;
			}
		})

        })

        $(document).on("click","#imprimir_carnets_lote",function(){
            var id = $(this).data('id');
            window.open('<?=base_url()?>imprimir/carnets/','','menubar=yes,toolbar=yes,width=800,height=600');
        })

        $(document).on("click","#imprimir_platea",function(){
            var id = $(this).data('id');
            window.open('<?=base_url()?>imprimir/platea/'+id,'','menubar=yes,toolbar=yes,width=800,height=600');
        })

        $("#grupo-select").change(function(){
            var grupo = $(this).val();
            $("#grupo-categorias").slideUp();
            $("#grupo-actividades").slideUp();
            $("#grupo-comisiones").slideUp();

		if ( grupo == "soccomision" || grupo == "titcomision" ) {
            		$("#grupo-comisiones").slideDown();
		}

            $("#grupo-"+grupo).slideDown();
        })
        <?
        if($this->uri->segment(3) == 'nuevo'){
        ?>
        $("#envios-step1").submit(function(){
            $("#envios-continuar").prop('disabled',true);
            $("#envios-continuar").html('Procesando <i class="fa fa-spin fa-spinner"></i>')
            var activ = $("#activ-select").val();
	    if ( activ == " " ) {
		alert ("Debe Elegir una condicion de estado");
            	$("#envios-continuar").prop('disabled',false);
            	$("#envios-continuar").html('Procesando <i class="fa fa-arrow-right"></i>')
		return false;
		}
            var grupo = $("#grupo-select").val();
            var data;
            var titulo = $("#envio-titulo").val();
            var url_link = $("#url_link").val();
	    if ( grupo == "soccomision" || grupo == "titcomision" ) {
			data = $("#comisiones-select").val();
		} else {
            		data = $("#"+grupo+"-select").val();
		}

            $.post("<?=base_url()?>admin/envios/agregar",{titulo:titulo,grupo:grupo,data:data,activ:activ,url_link:url_link})
            .done(function(data){
                if(data == 'no_mails'){
                    $("#step2").html('<div class="alert alert-danger">No se encontraron socios para el grupo seleccionado.</div>');
                    $("#envios-continuar").prop('disabled',false);
                    $("#envios-continuar").html('Continuar <i class="fa fa-arrow-right"></i>')
                }else{
                    $("#step2").html(data);
                    $("#envios-step1").slideUp();
                }
            })
        })
        $(document).on("submit","#envios-step2",function(e){
            var text = tinyMCE.activeEditor.getContent();
            var id = $("#envio_id").val();
            $.post("<?=base_url()?>admin/envios/guardar/"+id,{text:text})
            .done(function(){
                document.location.href = "<?=base_url()?>admin/envios/enviar/"+id;
            })
            e.preventDefault();
        })
        <?
        }
        ?>

        <?
        if($this->uri->segment(3) == 'editar'){
        ?>
        $("#envios-step1").submit(function(){
            $("#envios-continuar").prop('disabled',true);
            $("#envios-continuar").html('Procesando <i class="fa fa-spin fa-spinner"></i>')
            var grupo = $("#grupo-select").val();
            var data;
            var titulo = $("#envio-titulo").val();
            var url_link = $("#url_link").val();
            data = $("#"+grupo+"-select").val();
            $.post("<?=base_url()?>admin/envios/edicion/<?=$this->uri->segment(4)?>",{titulo:titulo,grupo:grupo,data:data,url_link:url_link})
            .done(function(data){
                if(data == 'no_mails'){
                    $("#step2").html('<div class="alert alert-danger">No se encontraron socios para el grupo seleccionado.</div>');
                    $("#envios-continuar").prop('disabled',false);
                    $("#envios-continuar").html('Continuar <i class="fa fa-arrow-right"></i>')
                }else{
                    $("#step2").html(data);
                    $("#envios-step1").slideUp();
                }
            })
        })
        $(document).on("submit","#envios-step2",function(e){
            var text = tinyMCE.activeEditor.getContent();
            var id = $("#envio_id").val();
            $.post("<?=base_url()?>admin/envios/guardar/"+id,{text:text})
            .done(function(){
                document.location.href = "<?=base_url()?>admin/envios/enviar/"+id;
            })
            e.preventDefault();
        })
        <?
        }
        ?>


        $(document).on('click','#del_confirm',function(){
            var msj = $(this).data('msj');
            var agree = confirm(msj);
            if(!agree){return false;}
        })

        function hide_cambio(){
            $("#cambio_correcto").slideUp();
        }

        $(document).on("click",".actividad_beca",function(){
            var tipo_beca = $(this).data('tbeca');
            var beca = $(this).data('beca');
            var id = $(this).data('id');
            angular.element("#modal_open").triggerHandler('click');
            $("#beca-tipo").val(tipo_beca);
            $("#beca-porcien").val(beca);
            $("#beca-id").val(id);
            return false;
        })
        $(document).on('submit','#form-beca',function(e){
            var tipo_beca = $("#beca-tipo").val();
            var id = $("#beca-id").val();
            var beca = $("#beca-porcien").val();
	    if ( tipo_beca == 1 || tipo_beca == 3 || tipo_beca == 5 || tipo_beca == 7 ) {
		if ( beca > 100 ) {
			alert ("No se puede poner un % mayor al 100%");
			return false;
		}
	    }
            e.preventDefault();
            $.post("<?=base_url()?>admin/actividades/becar",{id:id,beca:beca,tipo_beca:tipo_beca})
            .done(function(){
                angular.element("#modal_close").triggerHandler('click');
                $("#actividad_beca_"+id).data('beca',beca);
                $("#actividad_beca_"+id).data('tbeca',tipo_beca);
            })
        })

        function calcular_cuota(){
            var monto = $("#s_cate").find(':selected').attr('data-precio');
            var descuento = $("#descuento").val();
            var a_pagar = monto - ( monto*descuento/100 );
            $("#a_pagar").text(a_pagar);
        }
        <? if ($this->uri->segment(2) == 'socios' && ($this->uri->segment(3) == 'agregar' || $this->uri->segment(3) == 'editar') ) {
        ?>
        calcular_cuota();
        <?
        }
        ?>

        $("#s_cate").change(function(){calcular_cuota()});
        $("#descuento").keyup(function(){calcular_cuota()});

$("#load-debtarj-form").submit(function(){
        var marca = $("#marca").val();
        var fecha = $("#fecha").val();
        var url = "<?=$baseurl?>admin/debtarj/subearchivo" + "/" + marca + "/" + fecha

        $("#load-debtarj-form").attr("action",url);

        $("#load-debtarj-form").submit();

        return true;

})


$("#boton-deb").on("click", function(){
        var boton = $(this).data("text");
	if ( boton == "btnnuevo" ) {
        	var msg = "Seguro que desea agregar este nuevo debito?";
        	var url = "<?=$baseurl?>admin/debtarj/grabar/";
	} else {
        	var msg = "Seguro que desea actualizar este debito?";
		var url = "<?=$baseurl?>admin/debtarj/regrabar/";
	}


        var id_marca = $("#id_marca").val();
        var nro_tarjeta = $("#nro_tarjeta").val();
        var fecha_adhesion = $("#fecha_adhesion").val();
        var sid = $("#sid").val();

        if ( id_marca == 1 &&  ( nro_tarjeta < 4000000000000000 || nro_tarjeta > 4999999999999999 ) ) {
                alert ( "El nro de tarjeta no es de VISA!!!!");
                return false;
	}
        if ( id_marca == 2 &&  ( nro_tarjeta < 627620000000000000 || nro_tarjeta > 627620999999999999 )) {
                alert ( "El nro de tarjeta no es de COOPEPLUS!!!!");
                return false;
	}
        if ( id_marca == 3 &&  ( nro_tarjeta < 627401000000000000 || nro_tarjeta > 627401999999999999 )) {
                alert ( "El nro de tarjeta no es de VISA!!!!");
                return false;
	}

        var agree = confirm(msg);
        if(!agree){return false;};
        $("#reg-cargando").removeClass('hidden');

	if ( boton == "btnnuevo" ) {
        	$("#nvo_debtarj_form").attr("action",url);
        	$("#nvo_debtarj_form").submit();
	} else {
        	$("#edit_debtarj_form").attr("action",url);
        	$("#edit_debtarj_form").submit();
	}

        return true;
})


$("#gen_debtarj_form select").on("change", function(){
    var id_marca = $(this).val();
    if ( id_marca > 1 ) {
        $("#btn_total").show();
    } else {
        $("#btn_total").hide();
    }

})
$("#gen_debtarj_form button").on("click", function(){
        var boton = $(this).data("text");

	var id_marca = $("#id_marca").val();
	var periodo = $("#periodo").val();
	var flag = $("#flag").val();

        var agree = confirm("Seguro que desea "+boton+" el archivo de la marca "+id_marca+" ?");
        if(!agree){return false;}

	if ( flag == 1 ) {
        	var url = $(this).data("action") + "/" + id_marca + "/" + periodo + "/1";
	} else {
        	var url = $(this).data("action") + "/" + id_marca + "/" + periodo;
	}
        $("#gen_debtarj_form").attr("action",url);

        $("#gen_debtarj_form").submit();

        return true;
})

$("#contracargos_deb button").on("click", function(){
        var boton = $(this).data("text");


        var id_marca = $("#id_marca").val();
        var periodo = $("#periodo").val();
        var id_cabecera = $("#id_cabecera").val();
        var fecha_debito = $("#fecha_debito").val();
	if ( boton == "do" ) {
        	var nrotarjeta = $("nrotarjeta").val();
        	var nrorenglon = $("nrorenglon").val();
        	var importe = $("importe").val();
	}

        var url = $(this).data("action");
        $("#contracargos_deb").attr("action",url);

        $("#contracargos_deb").submit();

        return true;
})


$("#debtarj_botones_form button").on("click", function(){
        var boton = $(this).data("text");
	
	switch ( boton ) {	
		case "excel":
			var url = $(this).data("action");
			break;
		case "nuevo":
			var url = $(this).data("action");
			break;
	}

        $("#debtarj_botones_form").attr("action",url);

        $("#debtarj_botones_form").submit();

        return true;
})



        </script>
        <? if($this->uri->segment(3) == 'guardada' && $this->uri->segment(2) == 'configuracion'){ ?>
        <script type="text/javascript">setTimeout(hide_cambio,4000)</script>
        <? } ?>

    </body>
</html>
