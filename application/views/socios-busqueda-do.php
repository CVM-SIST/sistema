                    <div class="page page-profile">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong><span class="fa fa-group"></span> SOCIOS </strong></div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>DNI</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?
                                    foreach ($socios as $socio) {                                       
					if ( $socio->suspendido == 1 ) {
						$xestado = "SUSPENDIDO";
					} else {
						$xestado = "ACTIVO";
					}
                                    ?>
                                    <tr>
                                        <td><?=$socio->Id?></td>
                                        <td><?=$socio->dni?></td>
                                        <td><?=$socio->nombre.", ".$socio->apellido?> </td>
                                        <td><?=$xestado?> </td>
                                        <td>
						<a href="<?=base_url()?>admin/socios/editar/<?=$socio->Id?>">Editar</a> |
                        			<a href="<?=base_url()?>admin/socios/resumen/<?=$socio->Id?>">Ver Resumen</a>
                    			</td>
                                    </tr>                                    
                                    <?
                                    }
                                    ?>                              

                                </tbody>
                            </table>
                        </div>
                    </div>
