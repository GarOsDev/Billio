<?php

// Se incluye archivo PHP  db_facturas.php encargado de la conexion a BBDD
require_once('api_gestion/db_facturas.php');
$db_obj = new Facturasdb();

/* Funciones select */
$select_clientes = $db_obj->getClientes();
$select_producto = $db_obj->getProductos();
$select_facturas = $db_obj->getFacturas();
$select_facturasGral = $db_obj->getFacturasGral();


$contador = 0; // Variable que maneja cada una de las posibles facturas asociadas a un cliente
$ext = ""; // Variable donde almaceno la extension del archivo subido a la carpeta imagenes_productos desde formulario
$verif = false; // Variable booleana que controla imagen principal de la web


// URL GRAL. CAMBIAR SEGUN DISPOSICIÓN DE CARPETAS (cambiar tambien indices de URL en api_facturas.php segun corresponda)
$URL_BASE = "http://localhost";



/* ********************************************************** VISUALIZAR ********************************************************** */
/* 

	Grupo de peticiones API GET encargadas de devolver los datos solicitados dese la web. Tanto listas como elementos concretos aplicando función
	callAPI que realiza peticion GET a la API

*/

if (isset($_GET['vis_clientes'])) {

	$json = callAPI("$URL_BASE/practica_apirest_facturas/api_gestion/clientes");
	$resp = json_decode($json);
} else if (isset($_POST["vis_cliente"])) {

	$id_cliente = $_POST["id_cli"];

	$json = callAPI("$URL_BASE/practica_apirest_facturas/api_gestion/cliente/$id_cliente");
	$resp = json_decode($json);

	if (count($resp) == 0) {
		$resp = $db_obj->getCliente_SF($id_cliente);
	}
} else if (isset($_GET["vis_facturas"])) {

	$json = callAPI("$URL_BASE/practica_apirest_facturas/api_gestion/facturas");
	$resp = json_decode($json);
} else if (isset($_POST["vis_factura"])) {

	$id_factura = $_POST["id_fac"];

	$json = callAPI("$URL_BASE/practica_apirest_facturas/api_gestion/factura/$id_factura");
	$resp = json_decode($json);
} else if (isset($_GET["vis_productos"])) {

	$json = callAPI("$URL_BASE/practica_apirest_facturas/api_gestion/productos");
	$resp = json_decode($json);
} else if (isset($_POST["vis_producto"])) {

	$id_producto = $_POST["id_prod"];

	$json = callAPI("$URL_BASE/practica_apirest_facturas/api_gestion/producto/$id_producto");
	$resp = json_decode($json);
}


/* ********************************************************** ELIMINAR ********************************************************** */

/* 

	Grupo de peticiones encargadas eliminar cada registro existente en BBDD. Se utiliza la función delete_API para, pasandole la
	URL correspondiente con un id determinado, ejecute la peticion a la API de tipo DELETE
	
*/

if (isset($_POST["del_cliente"])) {

	$id_cliente_del = $_POST["id_cli_del"];

	$json = delete_API("$URL_BASE/practica_apirest_facturas/api_gestion/cliente/$id_cliente_del");
	$resp = json_decode($json);

	$notificaciones[] = "Cliente eliminado";
} else if (isset($_POST["del_factura"])) {

	$id_factura_del = $_POST["id_fac_del"];

	$json = delete_API("$URL_BASE/practica_apirest_facturas/api_gestion/factura/$id_factura_del");
	$resp = json_decode($json);

	$notificaciones[] = "Factura eliminada";
} else if (isset($_POST["del_producto"])) {

	$id_producto_del = $_POST["id_prod_del"];

	$json = delete_API("$URL_BASE/practica_apirest_facturas/api_gestion/producto/$id_producto_del");
	$resp = json_decode($json);

	$notificaciones[] = "Producto eliminado";
}

/* ********************************************************** MODIFICACIÓN ********************************************************** */

/* 

	Grupo de peticiones encargadas modificar cada registro . En este caso se utiliza el método put_API para, pasandole los parametros
	correspondientes (url con id especifico y datos a cambiar), realice una peticion UPDATE gestionada por api_facturas.php
	
*/

if (isset($_POST["modif_cliente"])) {

	$id_cliente_mod = $_POST["id_cli_mod"];

	$datos["nombre"] = $_POST['nom_cli'];
	$datos["CIF"] = $_POST['cif_cli'];
	$datos["direccion"] = $_POST['dir_cli'];
	$datos["telefono"] = $_POST['tel_cli'];

	$datos = json_encode($datos);

	$json = put_API("$URL_BASE/practica_apirest_facturas/api_gestion/cliente/$id_cliente_mod", $datos);
	$resp = json_decode($json);

	$notificaciones[] = "Cliente modificado";

} else if (isset($_POST["modif_factura"])) {

	$fecha = date("Y-m-d", strtotime($_POST['fec_fac']));

	$id_factura_mod = $_POST["id_fac_mod"];

	$datos["codigo_factura"] = $_POST['cod_fac'];
	$datos["fecha_factura"] = $fecha;

	$datos = json_encode($datos);

	$json = put_API("$URL_BASE/practica_apirest_facturas/api_gestion/factura/$id_factura_mod", $datos);
	$resp = json_decode($json);

	$notificaciones[] = "Factura modificada";
} else if (isset($_POST["modif_producto"])) {

	if (isset($_FILES["file_up"])) { // Logica para el almacenamiento de la imagen seleccionada en formulario

		$directorio = "recursos/imagenes_productos/"; // Directorio de destino
		$ruta_archivo = "";

		$extension = $_FILES['file_up']['name'];
		$partes = explode(".", $extension);
		$ext = end($partes); // Extension del archivo

		if (is_uploaded_file($_FILES["file_up"]["tmp_name"])) {

			$nombre_archivo = 'img_up_' . date("Ymd-His") . '.' . $ext;
			$ruta_archivo = $directorio . $nombre_archivo;

			move_uploaded_file($_FILES["file_up"]["tmp_name"], $ruta_archivo); // movemos el archivo y le damos nombre

		} else {
			echo "Error subiendo el archivo";
		}
	}

	$id_producto_mod = $_POST["id_prod_mod"];

	$datos["nombre"] = $_POST['nom_prod'];
	$datos["precio"] = $_POST['prec_prod'];
	$datos["stock"] = $_POST['stck_prod'];
	$datos["imagen"] = $ruta_archivo;

	$datos = json_encode($datos);

	$json = put_API("$URL_BASE/practica_apirest_facturas/api_gestion/producto/$id_producto_mod", $datos);
	$resp = json_decode($json);

	$notificaciones[] = "Producto modificado";
}

/* ********************************************************** CREAR ********************************************************** */

/* 

	Grupo de peticiones encargadas crear nuevos registros . Se utiliza el método post_API que realiza una peticion cURL de tipo
	POST para así crear, mediante la funcion gestionada por la API, el registro con los valores pasados por fomulario desde este
	archivo
	
*/

if (isset($_POST["crear_cliente"])) {// variable $_POST recogida del formulario correspondiente



	$datos["nombre"] = $_POST['nom_cli'];
	$datos["CIF"] = $_POST['cif_cli'];
	$datos["direccion"] = $_POST['dir_cli'];
	$datos["telefono"] = $_POST['tel_cli'];

	if ($db_obj->comprobarCliente($_POST['nom_cli'], $_POST['cif_cli'])) {

		$notificaciones[] = "Cliente ya existente";
	} else {

		$datos = json_encode($datos);

		$json = post_API("$URL_BASE/practica_apirest_facturas/api_gestion/cliente", $datos);
		$resp = json_decode($json);

		$notificaciones[] = "Cliente creado con exito";
	}
} else if (isset($_POST["crear_factura"])) {

	$datos["id_cliente"] = $_POST["id_cli"];
	$datos["id_producto"] = $_POST["id_prod"];

	$fecha = date("Y-m-d", strtotime($_POST['fec_fac']));
	$datos["codigo_factura"] = $_POST['cod_fac'];
	$datos["fecha_factura"] = $fecha;

	$datos["cantidad"] = $_POST["cant"];

	if ($db_obj->comprobarFactura($_POST['cod_fac'])) {

		$notificaciones[] = "Factura ya existente";
	} else {

		$datos = json_encode($datos);


		$json = post_API("$URL_BASE/practica_apirest_facturas/api_gestion/factura", $datos);
		$resp = json_decode($json);

		$notificaciones[] = "Factura creada con exito";
	}
} else if (isset($_POST["crear_producto"])) {

	if (isset($_FILES["file_up"])) { // Logica para el almacenamiento de la imagen seleccionada en formulario

		$directorio = "recursos/imagenes_productos/"; // Directorio de destino
		$ruta_archivo = "";

		$extension = $_FILES['file_up']['name'];
		$partes = explode(".", $extension);
		$ext = end($partes); // Extension del archivo

		if (is_uploaded_file($_FILES["file_up"]["tmp_name"])) {

			$nombre_archivo = 'img_up_' . date("Ymd-His") . '.' . $ext;
			$ruta_archivo = $directorio . $nombre_archivo;

			move_uploaded_file($_FILES["file_up"]["tmp_name"], $ruta_archivo); // movemos el archivo y le damos nombre

		} else {
			echo "Ha habido un error subiendo el archivo";
		}
	}

	$datos["nombre"] = $_POST['nom_prod'];
	$datos["precio"] = $_POST['prec_prod'];
	$datos["stock"] = $_POST['stck_prod'];
	$datos["imagen"] = $ruta_archivo;


	if ($db_obj->comprobarProducto($datos["nombre"])) {
		$notificaciones[] = "Producto ya existente";
	} else {

		$datos = json_encode($datos);

		$json = post_API("$URL_BASE/practica_apirest_facturas/api_gestion/producto", $datos);
		$resp = json_decode($json);

		$notificaciones[] = "Producto creado con exito";
	}
}

/* ********************************************************** PETICIONES CURL ********************************************************** */

//PETICIONES GET (SELECT)

function callAPI($url)
{
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	$resp = curl_exec($ch);

	if (curl_errno($ch) == 0) {

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 200) {
			return $resp;
		} else {
			return -1;
		}
	} else {

		return -2;
	}

	curl_close($ch);
}

// PETICIONES DELETE

function delete_API($url)
{
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

	$resp = curl_exec($ch);


	if (curl_errno($ch) == 0) {

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 200) {
			return $resp;
		} else {
			return -1;
		}
	} else {

		return -2;
	}

	curl_close($ch);
}

// PETICIONES PUT (UPDATE)

function put_API($url, $datos = "")
{
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $datos);

	$resp = curl_exec($ch);

	if (curl_errno($ch) == 0) {

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 200) {
			return $resp;
		} else {
			return -1;
		}
	} else {

		return -2;
	}

	curl_close($ch);
}

// PETICIONES POST (INSERT)

function post_API($url, $datos = "")
{
	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $datos);

	$resp = curl_exec($ch);

	if (curl_errno($ch) == 0) {

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if ($http_code == 200) {
			return $resp;
		} else {
			return -1;
		}
	} else {

		return -2;
	}

	curl_close($ch);
}

?>

<!-- ********************************************************** CUERPO DE LA INTERFAZ HTML ********************************************************** -->

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gestor de Facturas v1.0</title>
	<link rel="stylesheet" href="recursos/estilo_interfaz/interfaz_style.css">
</head>

<body>
	<div class="titulos">
		<h1><a href="<?= $_SERVER['PHP_SELF'] ?>">Sistema de Gestión de Facturas v1.0</a></h1>
	</div>
	<div>
		<nav> <!-- MENU PPAL -->
			<ul class="menu">
				<li><a href="<?= $_SERVER['PHP_SELF'] ?>?opcion=visualizar" class="btn btn-white btn-animate">Visualizar</a></li>
				<li><a href="<?= $_SERVER['PHP_SELF'] ?>?opcion=crear" class="btn btn-white btn-animate">Crear</a></li>
				<li><a href="<?= $_SERVER['PHP_SELF'] ?>?opcion=modificar" class="btn btn-white btn-animate">Modificar</a></li>
				<li><a href="<?= $_SERVER['PHP_SELF'] ?>?opcion=eliminar" class="btn btn-white btn-animate">Eliminar</a></li>
			</ul>
		</nav>
	</div>

	<div class="notificaciones">
		<?php
		if (isset($notificaciones) && count($notificaciones) != 0) {
			foreach ($notificaciones as $n) {
		?>
				<h2><?= $n ?></h2>
				<img class="man_looking" src="recursos/estilo_interfaz/man_looking_up.png">
		<?php
			}
		}
		?>
	</div>

	<div class="contenedor">

		<div class="seleccion">

			<?php

			if (isset($_GET["opcion"])) {

				$verif = true;

				switch ($_GET["opcion"]) { // Utilizo estructura switch-case para gestionar cada una de las secciones de la página solicitadas desde menú

						/* ************************************ VISUALIZAR DATOS ************************************ */

					case 'visualizar':

			?>
						<h3 class="titulo_seccion">Ver Registros</h3>

						<table>
							<tr>
								<th>Clientes</th>
								<th>Facturas</th>
								<th>Productos</th>
							</tr>
							<tr>
								<td>
									<li class="listing"><a href="<?= $_SERVER['PHP_SELF'] ?>?vis_clientes">Listar Clientes</a></li>
								</td>
								<td>
									<li class="listing"><a href="<?= $_SERVER['PHP_SELF'] ?>?vis_facturas">Listar Facturas</a></li>
								</td>
								<td>
									<li class="listing"><a href="<?= $_SERVER['PHP_SELF'] ?>?vis_productos">Listar Productos</a></li>
								</td>
							</tr>
							<tr>
								<td class="search">Selección por Cliente</td>
								<td class="search">Selección por Id-Codigo Factura</td>
								<td class="search">Selección por Id Producto</td>
							</tr>
							<tr>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">
										<select class="cliente" name="id_cli">
											<?php
											foreach ($select_clientes as $cliente) {
												echo "<option value='{$cliente['id_cliente']}'>$cliente[id_cliente]. - $cliente[nombre]</option>";
											}
											?>
										</select>
										<input type="submit" name="vis_cliente" value="Ver Cliente" class="submit">
									</form>
								</td>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">
										<select class="factura" name="id_fac">
											<?php
											foreach ($select_facturas as $factura) {
												echo "<option value='{$factura['id_factura']}'>$factura[id_factura] - $factura[codigo]</option>";
											}
											?>
										</select>
										<input type="submit" name="vis_factura" value="Ver Factura" class="submit">
									</form>
								</td>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">
										<select class="producto" name="id_prod">
											<?php
											foreach ($select_producto as $producto) {
												echo "<option value='{$producto['id_producto']}'>$producto[id_producto]. - $producto[nombre]</option>";
											}
											?>
										</select>
										<input type="submit" name="vis_producto" value="Ver Producto" class="submit">
									</form>
								</td>
							</tr>

						</table>
					<?php

						break;

						/* ************************************ CREAR DATOS ************************************ */

					case 'crear':


					?>
						<h3 class="titulo_seccion">Crear Registros</h3>
						<table>
							<tr>
								<th>Clientes</th>
								<th>Facturas</th>
								<th>Productos</th>
							</tr>
							<tr>
								<td class="search">Crear Cliente</td>
								<td class="search">Crear Factura</td>
								<td class="search">Crear Producto</td>
							</tr>

							<tr>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">

										<input type="text" class="cliente" name="nom_cli" placeholder="nombre cliente">
										<input type="text" class="cliente" name="cif_cli" placeholder="cif">
										<input type="text" class="cliente" name="dir_cli" placeholder="direccion">
										<input type="text" class="cliente" name="tel_cli" placeholder="telefono">

										<input type="submit" name="crear_cliente" value="Dar de Alta" class="submit">
									</form>
								</td>

								<td>

									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">

										<label for="select_cliente">Seleccione Cliente</label>
										<select class="factura" name="id_cli">
											<?php
											foreach ($select_clientes as $cliente) {
												echo "<option value='{$cliente['id_cliente']}'>{$cliente['nombre']}</option>";
											}
											?>
										</select>

										<label for="select_producto">Seleccione Producto</label>
										<select class="factura" name="id_prod">
											<?php
											foreach ($select_producto as $producto) {
												echo "<option value='{$producto['id_producto']}'>{$producto['nombre']}</option>";
											}
											?>
										</select>

										<input type="text" class="factura" name="cod_fac" placeholder="codigo factura">
										<input type="date" class="factura" name="fec_fac" placeholder="aaaa-mm-dd">

										<input type="text" class="factura" name="cant" placeholder="cantidad">

										<input type="submit" name="crear_factura" value="Dar de Alta" class="submit">
									</form>
								</td>

								<td>

									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form" enctype="multipart/form-data">

										<input type="text" class="producto" name="nom_prod" placeholder="nombre producto">
										<input type="text" class="producto" name="prec_prod" placeholder="precio">
										<input type="text" class="producto" name="stck_prod" placeholder="stock">

										<input type="file" name="file_up" id="file_img" class="inputfile_img" />
										<label for="file_img">Subir IMG</label>

										<input type="submit" name="crear_producto" value="Dar de Alta" class="submit">
									</form>
								</td>
							</tr>

						</table>
					<?php

						break;

						/* ************************************ MODIFICAR DATOS ************************************ */

					case 'modificar':


					?>
						<h3 class="titulo_seccion">Modificar Registros</h3>
						<table>
							<tr>
								<th>Clientes</th>
								<th>Facturas</th>
								<th>Productos</th>
							</tr>
							<tr>
								<td class="search">Modificar Cliente</td>
								<td class="search">Modificar Factura</td>
								<td class="search">Modificar Producto</td>
							</tr>

							<tr>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">

										<select class="cliente" name="id_cli_mod">
											<?php
											foreach ($select_clientes as $cliente) {
												echo "<option value='{$cliente['id_cliente']}'>$cliente[id_cliente]. - $cliente[nombre]</option>";
											}
											?>
										</select>

										<input type="text" class="cliente" name="nom_cli" placeholder="nuevo nombre cliente">
										<input type="text" class="cliente" name="cif_cli" placeholder="cif">
										<input type="text" class="cliente" name="dir_cli" placeholder="direccion">
										<input type="text" class="cliente" name="tel_cli" placeholder="telefono">

										<input type="submit" name="modif_cliente" value="Cambiar" class="submit">
									</form>
								</td>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">
										<select class="factura" name="id_fac_mod">
											<?php
											foreach ($select_facturasGral as $facturaGral) {
												echo "<option value='{$facturaGral['id_factura']}'>$facturaGral[id_factura]</option>";
											}
											?>
										</select>

										<input type="text" class="factura" name="cod_fac" placeholder="codigo factura">
										<input type="date" class="factura" name="fec_fac" placeholder="aaaa-mm-dd">

										<input type="submit" name="modif_factura" value="Cambiar" class="submit">
									</form>
								</td>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form" enctype="multipart/form-data">

										<select class="producto" name="id_prod_mod">
											<?php
											foreach ($select_producto as $producto) {
												echo "<option value='{$producto['id_producto']}'>$producto[id_producto]. - $producto[nombre]</option>";
											}
											?>
										</select>

										<input type="text" class="producto" name="nom_prod" placeholder="nuevo nombre producto">
										<input type="text" class="producto" name="prec_prod" placeholder="precio">
										<input type="text" class="producto" name="stck_prod" placeholder="stock">

										<input type="file" name="file_up" id="file_img" class="inputfile_img" />
										<label for="file_img">Subir IMG</label>

										<input type="submit" name="modif_producto" value="Cambiar" class="submit">
									</form>
								</td>
							</tr>

						</table>
					<?php

						break;

						/* ************************************ ELIMINAR DATOS ************************************ */

					case 'eliminar':

					?>
						<h3 class="titulo_seccion">Visualizar Registros</h3>
						<table>
							<tr>
								<th>Clientes</th>
								<th>Facturas</th>
								<th>Productos</th>
							</tr>
							<tr>
								<td class="search">Eliminar Cliente</td>
								<td class="search">Eliminar Factura</td>
								<td class="search">Eliminar Producto</td>
							</tr>

							<tr>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">

										<select class="cliente" name="id_cli_del">
											<?php
											foreach ($select_clientes as $cliente) {
												echo "<option value='{$cliente['id_cliente']}'>$cliente[id_cliente]. - $cliente[nombre]</option>";
											}
											?>
										</select>

										<input type="submit" name="del_cliente" value="Eliminar" class="submit">
									</form>
								</td>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">

										<select class="factura" name="id_fac_del">
											<?php
											foreach ($select_facturas as $factura) {
												echo "<option value='{$factura['id_factura']}'>$factura[id_factura] - $factura[codigo]</option>";
											}
											?>
										</select>

										<input type="submit" name="del_factura" value="Eliminar" class="submit">
									</form>
								</td>
								<td>
									<form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="browser_form">

										<select class="producto" name="id_prod_del">
											<?php
											foreach ($select_producto as $producto) {
												echo "<option value='{$producto['id_producto']}'>$producto[id_producto]. - $producto[nombre]</option>";
											}
											?>
										</select>
										<input type="submit" name="del_producto" value="Eliminar" class="submit">
									</form>
								</td>
							</tr>

						</table>

			<?php

						break;
				}
			}
			?>

		</div>


	</div>

	<!-- *************************************************************** RESULTADOS *************************************************************** -->

	<!-- En esta parte del codigo, se muestran los resultados proporcionados por la API una vez mandado el formulario correspondiente -->

	<div class="resultados">
		<?php

		if (isset($_GET['vis_clientes'])) {

			$verif = true;
		?>
			<h2 class="viendo">Ud. esta viendo: Lista de Clientes</h2>
			<?php
			foreach ($resp as $c) {
			?>
				<table>
					<tr>
						<th>Id Cliente</th>
						<th>Nombre</th>
						<th>CIF</th>
						<th>Dirección</th>
						<th>Teléfono</th>

					</tr>
					<tr>
						<td><?= $c->id_cliente ?></td>
						<td><?= $c->nombre ?></td>
						<td><?= $c->CIF ?></td>
						<td><?= $c->direccion ?></td>
						<td><?= $c->telefono ?></td>
					</tr>
				</table>

			<?php
			}
		} else if (isset($_POST['vis_cliente'])) {
			$verif = true;

			if (isset($resp[0]->codigo_factura)) {

			?>
				<h2 class="viendo">Ud. esta viendo: Cliente <?= $resp[0]->nombre ?></h2>
				<?php

				?>

				<table class="tg">
					<thead>
						<tr>
							<th class="tg-0lax">Id</th>
							<th class="tg-0lax">Nombre</th>
							<th class="tg-0lax">CIF</th>
							<th class="tg-0lax">Dirección</th>
							<th class="tg-0lax">Teléfono</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tg-0lax" rowspan=<?= $contador ?>><?= $resp[0]->id_cliente ?></td>
							<td class="tg-0lax"><?= $resp[0]->nombre ?></td>
							<td class="tg-0lax"><?= $resp[0]->CIF ?></td>
							<td class="tg-0lax"><?= $resp[0]->direccion ?></td>
							<td class="tg-0lax"><?= $resp[0]->telefono ?></td>
						</tr>
						<tr>
							<td class="tg-0lax" rowspan=<?= $contador ?>></td>
							<th class="tg-0lax" colspan="3">Facturas Asociadas</th>
						</tr>
						<tr>
							<td class="tg2" colspan="2">Codigo Factura (Ref)</td>
							<td class="tg2">Fecha Factura</td>
						</tr>
						<?php
						foreach ($resp as $c) {
							$contador++;
						?>
							<tr>

								<td class="tg-0lax" colspan="2"><?= $c->codigo_factura ?></td>
								<td class="tg-0lax"><?= $c->fecha_factura ?></td>
							</tr>
						<?php
						}
						?>
					</tbody>
				</table>

			<?php

			} else {

			?>
				<h2 class="viendo">Ud. esta viendo: Cliente <?= $resp[0]["nombre"] ?></h2>
				<table class="tg">
					<thead>
						<tr>
							<th class="tg-0lax">Id</th>
							<th class="tg-0lax">Nombre</th>
							<th class="tg-0lax">CIF</th>
							<th class="tg-0lax">Dirección</th>
							<th class="tg-0lax">Teléfono</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="tg-0lax" rowspan=<?= $contador ?>><?= $resp[0]["id_cliente"] ?></td>
							<td class="tg-0lax"><?= $resp[0]["nombre"] ?></td>
							<td class="tg-0lax"><?= $resp[0]["CIF"] ?></td>
							<td class="tg-0lax"><?= $resp[0]["direccion"] ?></td>
							<td class="tg-0lax"><?= $resp[0]["telefono"] ?></td>
						</tr>
						<tr>
							<td class="tg-0lax" rowspan=<?= $contador ?>></td>
							<th class="tg-0lax" colspan="3">Facturas Asociadas</th>
						</tr>
						<tr>
							<td class="tg2" colspan="2">Codigo Factura (Ref)</td>
							<td class="tg2">Fecha Factura</td>
						</tr>

						<tr>

							<td class="tg-0lax" colspan="3">Cliente sin facturas asociadas</td>
						</tr>

					</tbody>
				</table>

			<?php

			}
		} else if (isset($_GET['vis_facturas'])) {
			$verif = true;

			?>
			<h2 class="viendo">Ud. esta viendo: Lista de Facturas</h2>
			<?php
			foreach ($resp as $c) {
			?>

				<table>
					<tr>
						<th>Id Factura</th>
						<th>Código</th>
						<th>Fecha</th>
						<th>Cliente</th>
						<th>Total Productos(€)</th>
					</tr>
					<tr>
						<td><?= $c->id_factura ?></td>
						<td><?= $c->codigo ?></td>
						<td><?= $c->fecha_factura ?></td>
						<td><?= $c->nombre ?></td>
						<td><?= $c->total ?></td>
					</tr>
				</table>

			<?php
			}
		} else if (isset($_POST['vis_factura'])) {
			$verif = true;
			?>
			<h2 class="viendo">Ud. esta viendo: Factura #<?= $resp[0]->codigo_factura?></h2>
			<table>
				<tr>
					<th>Id Factura</th>
					<th>Código</th>
					<th>Fecha</th>
					<th>Cliente</th>
					<th>CIF</th>
					<th>Producto</th>
					<th>Cantidad</th>
					<th>Imagen</th>
				</tr>
				<tr>
					<td><?= $resp[0]->id_factura ?></td>
					<td><?= $resp[0]->codigo_factura ?></td>
					<td><?= $resp[0]->fecha_factura ?></td>
					<td><?= $resp[0]->nombreCliente ?></td>
					<td><?= $resp[0]->CIF ?></td>
					<td><?= $resp[0]->nombre ?></td>
					<td><?= $resp[0]->cantidad ?></td>
					<td><img src="<?= $resp[0]->imagen_prod ?>" class="img_muestra"></td>
				</tr>
			</table>

			<?php

		} else if (isset($_GET['vis_productos'])) {
			$verif = true;

			?>
			<h2 class="viendo">Ud. esta viendo: Lista de Productos</h2>
			<?php
			foreach ($resp as $c) {
			?>

				<table>
					<tr>
						<th>Id Producto</th>
						<th>Nombre</th>
						<th>Precio</th>
						<th>Stock</th>
						<th>Imagen</th>

					</tr>
					<tr>
						<td><?= $c->id_producto ?></td>
						<td><?= $c->nombre ?></td>
						<td><?= $c->precio ?></td>
						<td><?= $c->stock ?></td>
						<td><img src="<?= $c->imagen ?>" class="img_muestra"></td>
					</tr>
				</table>

			<?php
			}
		} else if (isset($_POST['vis_producto'])) {
			$verif = true;
			?>
			<h2 class="viendo">Ud. esta viendo: Producto <?= $resp[0]->id_producto?> </h2>
			<?php
			foreach ($resp as $c) {
			?>

				<table>
					<tr>
						<th>Id Producto</th>
						<th>Nombre</th>
						<th>Precio</th>
						<th>Stock</th>
						<th>Imagen</th>

					</tr>
					<tr>
						<td><?= $c->id_producto ?></td>
						<td><?= $c->nombre ?></td>
						<td><?= $c->precio ?></td>
						<td><?= $c->stock ?></td>
						<td><img src="<?= $c->imagen ?>" class="img_muestra"></td>
					</tr>
				</table>

		<?php
			}
		}

		if (!$verif && !isset($notificaciones)) {
			echo "<img class='main_img' src='recursos/estilo_interfaz/invoice.svg'>";
		}

		?>



	</div>


	<footer class="foot">
		<p>&copy; 2024 Gestor de Facturas</p>
		<p>Desarrollado por Óscar García</p>
	</footer>



</body>

</html>