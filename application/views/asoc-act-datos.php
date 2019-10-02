<section class="page page-profile">
    <div class="panel panel-default">
        <div class="panel-heading"><strong><span class="fa fa-plus"></span> Editar Datos Socio</strong></div>
        <div class="panel-body">
                    <form class="form-horizontal ng-pristine ng-valid" action="<?=$baseurl?>admin/socios/act-datos-do/<?=$actividad?>/<?=$email?>/<?=$telefono?>/<?=$socio->Id?>" method="post">

                <div class="form-group">
                    <label for="" class="col-sm-2">Nombre</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="nombre" value="<?=$socio->nombre?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Apellido</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?=$socio->apellido?>" name="apellido">
                    </div>
                </div>                                 
                <div class="form-group">
                    <label for="" class="col-sm-2">Email</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?=$socio->mail?>" name="mail">
                    </div>
                </div>                                 
                <div class="form-group">
                    <label for="" class="col-sm-2">Telefono</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?=$socio->telefono?>" name="telefono">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Celular</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?=$socio->celular?>" name="celular">
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Categoría de Socio</label>
                    <div class="col-sm-10">
                        <span class=" ui-select">
                            <select name="categoria" id="categoria" style="margin:0px; width:100%; border:1px solid #cbd5dd; padding:8px 15px 7px 10px;">
                                <?
                                foreach ($categorias as $cat) {
                                ?>
                                    <option value="<?=$cat->Id?>" data-precio="<?=$cat->precio?>" <? if($cat->Id == $socio->categoria){echo 'selected';} ?>><?=$cat->nomb?></option>
                                <?
                                }
                                ?>
                            </select>
                        </span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="" class="col-sm-2">Socio #</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" value="<?=$socio->socio_n?>" name="socio_n">
                    </div>
                </div>


                <button type="submit" class="btn btn-success">Guardar Cambios</button>
            </form>
        </div>
    </div>
</section>                    
