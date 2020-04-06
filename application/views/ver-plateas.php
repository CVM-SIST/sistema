  <div class="page page-table" data-ng-controller="tableCtrlPlatea">
    <div class="panel panel-default table-dynamic">
      <div class="panel-heading"><strong><span class="fa fa-user"></span> PLATEAS</strong></div>        
        <div class="table-filters">
            <div class="row">
                <div class="col-sm-4 col-xs-6">
                    <form>
                        <input type="text"
                               placeholder="Buscar"
                               class="form-control"
                               data-ng-model="searchKeywords"
                               data-ng-keyup="search()">
                    </form>
                </div>
                <div class="col-sm-3 col-xs-6 filter-result-info">
                    <span>
                        Mostrando {{filteredStores.length}}/{{stores.length}} plateas
                    </span>              
                </div>
                <div class="col-sm-3 col-xs-12 filter-result-info" id="cargando_plateas" align="right">
                    <i class="fa fa-spinner fa-spin"></i> <strong>Cargando Listado de Plateas...</strong>
                </div>
            </div>
        </div>                
        <table class="table table-bordered table-striped table-responsive">
            <thead>

                <tr>
                    <th><div class="th">
                        ID
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('id') "
                              data-ng-class="{active: row == 'id'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-id') "
                              data-ng-class="{active: row == '-id'}"></span>
                    </div></th>
                    <th><div class="th">
                        Socio
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('socio') "
                              data-ng-class="{active: row == 'socio'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-socio') "
                              data-ng-class="{active: row == '-socio'}"></span>
                    </div></th>                    
                    <th><div class="th">
                        Actividad
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('actividad') "
                              data-ng-class="{active: row == 'actividad'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-actividad') "
                              data-ng-class="{active: row == '-actividad'}"></span>
                    </div></th>
                    <th><div class="th">
                        Descripcion
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('descripcion') "
                              data-ng-class="{active: row == 'descripcion'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-descripcion') "
                              data-ng-class="{active: row == '-descripcion'}"></span>
                    </div></th>
                    <th><div class="th">
                       Platea 
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('platea') "
                              data-ng-class="{active: row == 'platea'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-platea') "
                              data-ng-class="{active: row == '-platea'}"></span>
                    </div></th>
                    <th><div class="th">
                        Alta
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('fecha_alta') "
                              data-ng-class="{active: row == 'fecha_alta'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-fecha_alta') "
                              data-ng-class="{active: row == '-fecha_alta'}"></span>
                    </div></th>
                    <th><div class="th">
                        Importe
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('importe') "
                              data-ng-class="{active: row == 'importe'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-importe') "
                              data-ng-class="{active: row == '-importe'}"></span>
                    </div></th>
                    <th><div class="th">
                        Cuotas
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('cuotas') "
                              data-ng-class="{active: row == 'cuotas'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-cuotas') "
                              data-ng-class="{active: row == '-cuotas'}"></span>
                    </div></th>
                    <th><div class="th">
                        Valor Cuota
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('valor_cuota') "
                              data-ng-class="{active: row == 'valor_cuota'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-valor_cuota') "
                              data-ng-class="{active: row == '-valor_cuota'}"></span>
                    </div></th>
                    <th><div class="th">
                        Se Cobra
                        <span class="glyphicon glyphicon-chevron-up"
                              data-ng-click=" order('se_cobra') "
                              data-ng-class="{active: row == 'se_cobra'}"></span>
                        <span class="glyphicon glyphicon-chevron-down"
                              data-ng-click=" order('-se_cobra') "
                              data-ng-class="{active: row == '-se_cobra'}"></span>
                    </div></th>
                    <th><div class="th">
                        Opciones                        
                    </div></th>
                </tr>
            </thead>
            <tbody>


                <tr data-ng-repeat="store in currentPageStores">
                    <td>{{store.id}}</td>
                    <td>{{store.socio}}</td>
                    <td>{{store.actividad}}</td>
                    <td>{{store.descripcion}}</td>
                    <td>{{store.platea}}</td>
                    <td>{{store.fecha_alta}}</td>
                    <td>{{store.importe}}</td>
                    <td>{{store.cuotas}}</td>
                    <td>{{store.valor_cuota}}</td>
                    <td>{{store.se_cobra}}</td>
                    <td><a href="<?=base_url()?>admin/socios/plateas-act-datos/{{store.id}}">Editar</a> |
                      <a href="<?=base_url()?>admin/socios/plateas-baja/{{sotre.id}}">Eliminar</a> |
                      <a id="imprimir_platea" data-id="{{store.id}}" href="#">Imprimir</a>
		    </td>

                </tr>

                <?
                foreach ($plateas as $platea) {              
                ?>
                <!--<tr>
                    <td><?=$platea->id?></td>
                    <td><?=$platea->socio?></td>
                    
                    <td><?=$platea->actividad?></td>
                    <td><?=$platea->descripcion?></td>
                    <td><?=$platea->platea?></td>
                    <td><?=$platea->fecha_alta?></td>
                    <td><?=$platea->importe?></td>
                    <td><?=$platea->cuotas?></td>
                    <td><?=$platea->valor_cuota?></td>
                    <td><?=$platea->se_cobra?></td>
                    <td><a href="<?=base_url()?>admin/socios/plateas-act-datos/<?=$platea->id?>">Editar</a> | 
                      <a href="<?=base_url()?>admin/socios/plateas-baja/<?=$platea->id?>">Eliminar</a> |
                      <a id="imprimir_platea" data-id="<?=$platea->id?>" href="#">Imprimir</a></td>
                </tr>-->
                <?
                }
                ?>                              
            </tbody>
        </table>
        <footer class="table-footer">
            <div class="row">
                <div class="col-md-6 page-num-info">
                    <span>
                        Mostrar 
                        <select data-ng-model="numPerPage"
                                data-ng-options="num for num in numPerPageOpt"
                                data-ng-change="onNumPerPageChange()">
                        </select> 
                        plateas por página
                    </span>
                </div>
                <div class="col-md-6 text-right pagination-container">
                    <pagination class="pagination-sm"
                                page="currentPage"
                                total-items="filteredStores.length"
                                max-size="4"
                                on-select-page="select(page)"
                                items-per-page="numPerPage"
                                rotate="false"
                                boundary-links="true"></pagination>
                </div>
            </div>
        </footer>      
    </div>
  </div>
