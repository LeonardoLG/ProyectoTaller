<?php
	include('is_logged.php');//Archivo verifica que el usario que intenta acceder a la URL esta logueado
	/* Connect To Database*/
	require_once ("../config/db.php");//Contiene las variables de configuracion para conectar a la base de datos
	require_once ("../config/conexion.php");//Contiene funcion que conecta a la base de datos

	$action = (isset($_REQUEST['action'])&& $_REQUEST['action'] !=NULL)?$_REQUEST['action']:'';
	if (isset($_GET['id'])){
		$id_producto=intval($_GET['id']);
		$query=mysqli_query($con, "select * from detalle_factura where curso_idcurso='".$id_producto."'");
		$count=mysqli_num_rows($query);
		if ($count==0){
			if ($delete1=mysqli_query($con,"DELETE FROM curso WHERE idcurso='".$id_producto."'")){
			?>
				<div class="alert alert-success alert-dismissible" role="alert">
			  	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  	<strong>Aviso!</strong> Datos eliminados exitosamente.
				</div>
			<?php
			}else {
			?>
				<div class="alert alert-danger alert-dismissible" role="alert">
			  	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  	<strong>Error!</strong> Lo siento algo ha salido mal intenta nuevamente.
				</div>
			<?php

			}
		} else {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
			  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			  <strong>Error!</strong> No se pudo eliminar éste  curso. Existen cotizaciones vinculadas a éste curso.
			</div>
			<?php
		}



	}
	if($action == 'ajax'){
		// escaping, additionally removing everything that could be (html/javascript-) code
		$q = mysqli_real_escape_string($con,(strip_tags($_REQUEST['q'], ENT_QUOTES)));
		$aColumns = array('idcurso', 'nombre');//Columnas de busqueda
		$sTable = "curso LEFT JOIN profesor on (curso.profesor_idprofesor=profesor.idprofesor)";
		$sWhere = "";
		if ( $_GET['q'] != "" )
		{
			$sWhere = "WHERE (";
			for ( $i=0 ; $i<count($aColumns) ; $i++ )
			{
				$sWhere .= "curso.".$aColumns[$i]." LIKE '%".$q."%' OR ";
			}
			$sWhere = substr_replace( $sWhere, "", -3 );
			$sWhere .= ')';
		}
		$sWhere.="order by idcurso";
		include 'pagination.php'; //include pagination file
		//pagination variables
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 10; //how much records you want to show
		$adjacents  = 4; //gap between pages after number of adjacents
		$offset = ($page - 1) * $per_page;
		//Count the total number of row in your table*/
		$count_query=mysqli_query($con, "SELECT count(*) AS numrows FROM $sTable  $sWhere");
		$row= mysqli_fetch_array($count_query);
		$numrows = $row['numrows'];
		$total_pages = ceil($numrows/$per_page);
		$reload = './cursos.php';
		//main query to fetch the data
		$sql="SELECT idcurso  , curso.nombre as curso,
		 							estado, precio,
									profesor.nombre as nom_pro, profesor.apellido as ape_pro
					FROM  $sTable $sWhere LIMIT $offset,$per_page";

		$query = mysqli_query($con, $sql);
		//loop through fetched data
		if ($numrows>0){

			?>
			<div class="table-responsive">
			  <table class="table">
				<tr  class="info">
					<th>Código</th>
					<th>Curso</th>
					<th>Estado</th>
					<th>Profesor</th>
					<th class='text-right'>Precio</th>
					<th class='text-right'>Acciones</th>

				</tr>
				<?php
				while ($row=mysqli_fetch_array($query)){
						$id_producto=$row['idcurso'];
						$nombre_producto=$row['curso'];
						$status_producto=$row['estado'];
						if ($status_producto==1){
							$estado="Activo";
						}else {
							$estado="Inactivo";
						}
						$precio_producto=$row['precio'];
						$profesor=$row['nom_pro']." ".$row["ape_pro"];
					?>

					<input type="hidden" value="<?php echo $codigo_producto;?>" id="codigo_producto<?php echo $id_producto;?>">
					<input type="hidden" value="<?php echo $nombre_producto;?>" id="nombre_producto<?php echo $id_producto;?>">
					<input type="hidden" value="<?php echo $estado;?>" id="estado<?php echo $id_producto;?>">
					<input type="hidden" value="<?php echo number_format($precio_producto,2,'.','');?>" id="precio_producto<?php echo $id_producto;?>">
					<tr>

						<td><?php echo $id_producto; ?></td>
						<td ><?php echo $nombre_producto; ?></td>
						<td><?php echo $estado;?></td>
						<td><?php echo $profesor;?></td>
						<td>$<span class='pull-right'><?php echo number_format($precio_producto,2);?></span></td>
					<td ><span class="pull-right">
					<a href="#" class='btn btn-default' title='Editar Curso' onclick="obtener_datos('<?php echo $id_producto;?>');" data-toggle="modal" data-target="#myModal2"><i class="glyphicon glyphicon-edit"></i></a>
					<a href="#" class='btn btn-default' title='Borrar Curso' onclick="eliminar('<?php echo $id_producto; ?>')"><i class="glyphicon glyphicon-trash"></i> </a></span></td>

					</tr>
					<?php
				}
				?>
				<tr>
					<td colspan=6><span class="pull-right"><?
					 echo paginate($reload, $page, $total_pages, $adjacents);
					?></span></td>
				</tr>
			  </table>
			</div>
			<?php
		}
	}
?>
