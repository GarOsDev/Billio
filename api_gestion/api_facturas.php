<?php

// Archivo API encargado de gestionar el tipo de peticion por URL a través de cURL ejecutando la funcion correspondiente contra BBDD

require_once("db_facturas.php");

$url_array=explode("/",$_SERVER["REQUEST_URI"]); // Creo array a partir de la URL proporcionada, separando por "/"
$method=strtoupper($_SERVER["REQUEST_METHOD"]); // Identifico el tipo de peticion solicitada por usuario



/* **************************** ÍNDICES URL ************************** */
// (Cambiar según organizacion correspondiente)

$url_base = 2;
$url_peticion = 3; //Peticiones generales: clientes, facturas, productos
$url_indice = 4; // para peticiones de índice concreto

//**************************************************************** */

$indice_url=strtolower($url_array[$url_base]);
$db_obj=new Facturasdb();

if(isset($indice_url)){
	switch($method){

		case 'GET': // ************************************************************** PETICIONES POR NAVEGADOR GET
			
			if($url_array[$url_peticion] == "facturas"){
			
				$res=$db_obj->getFacturas();
				header('Content-type: application/json, charset=UTF-8');
				echo json_encode($res);
				
			}else if($url_array[$url_peticion] == "factura" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
				
				$id_factura = strtolower($url_array[$url_indice]);
				
				$res=$db_obj->getFactura($id_factura);
				header('Content-type: application/json, charset=UTF-8');
				echo json_encode($res);
				
			}else if($url_array[$url_peticion] == "clientes"){
			
				$res=$db_obj->getClientes();
				header('Content-type: application/json, charset=UTF-8');
				echo json_encode($res);
				
			}else if($url_array[$url_peticion] == "cliente" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
				
				$id_cliente = strtolower($url_array[$url_indice]);
				
				$res=$db_obj->getCliente($id_cliente);
				
				header('Content-type: application/json, charset=UTF-8');
				echo json_encode($res);

			}else if($url_array[$url_peticion] == "productos"){
			
				$res=$db_obj->getProductos();
				header('Content-type: application/json, charset=UTF-8');
				echo json_encode($res);
				
			}else if($url_array[$url_peticion] == "producto" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
				
				$id_producto = strtolower($url_array[$url_indice]);
				$res=$db_obj->getProducto($id_producto);
				
				header('Content-type: application/json, charset=UTF-8');
				echo json_encode($res);
				
			}else{
				http_response_code(404);
        		echo "sin respuesta";
			}
			
			break;
		
		case 'DELETE': // ************************************************************** PETICIONES DELETE
		
			echo "Prueba Delete";
			
			if($url_array[$url_peticion] == "factura" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
				
				$id_factura = strtolower($url_array[$url_indice]);
				$res = $db_obj->deleteFactura($id_factura);
				
				if($res == 1){
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						'status':'ok',
						'message':'Borrado Exitoso';
					}";
				}else{
					http_response_code(404);
				}

			}else if($url_array[$url_peticion] == "cliente" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
		
				$id_cliente = strtolower($url_array[$url_indice]);
				$res = $db_obj->deleteCliente($id_cliente);
				
				if($res == 1){
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						'status':'ok',
						'message':'Borrado Exitoso';
					}";
				}else{
					http_response_code(404);
				}
			}else if($url_array[$url_peticion] == "producto" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
		
				$id_producto = strtolower($url_array[$url_indice]);
				$res = $db_obj->deleteProducto($id_producto);
				
				if($res == 1){
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						'status':'ok',
						'message':'Borrado Exitoso';
					}";
				}else{
					http_response_code(404);
				}
			}
			
			break;
			
		case 'PUT': // ************************************************************** PETICIONES UPDATE
		
			echo "Ha entrado por put";
			
			if($url_array[$url_peticion] == "factura" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){
			
				$datos=json_decode(file_get_contents(filename: 'php://input'),true);

				$id_fac = strtolower($url_array[$url_indice]);
				$res = $db_obj->putFactura($id_fac,$datos);
				
				if($res == 1){
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						'status':'ok',
						'message':'Actualizacion correcta';
					}";
				}else{
					http_response_code(404);
				}
			
			}else if($url_array[$url_peticion] == "cliente" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){

				$datos=json_decode(file_get_contents(filename: 'php://input'),true);
		
				$id_cliente = strtolower($url_array[$url_indice]);
				$res = $db_obj->putCliente($id_cliente,$datos);
				
				if($res == 1){
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						'status':'ok',
						'message':'Actualizacion correcta';
					}";
				}else{
					http_response_code(404);
				}
			}else if($url_array[$url_peticion] == "producto" && isset($url_array[$url_indice]) && $url_array[$url_indice] != ""){

				$datos=json_decode(file_get_contents(filename: 'php://input'),true);
		
				$id_producto = strtolower($url_array[$url_indice]);
				$res = $db_obj->putProducto($id_producto,$datos);
				
				if($res == 1){
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						'status':'ok',
						'message':'Actualizacion correcta';
					}";
				}else{
					http_response_code(404);
				}
			}
			
			break;

		case 'POST': // ************************************************************** PETICIONES INSERT
		
			echo "Ha entrado por POST";
			
			if($url_array[$url_peticion] == "factura"){
			
				$datos=json_decode(file_get_contents(filename: 'php://input'),true);
				$res=$db_obj->postFactura($datos);
				
				if($res==1){
					
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						\"status':\"ok\",
						\"message':\"Creacion de registro correcto\"
					}";
					
				}else{
					http_response_code(404);
				}
				
			}else if($url_array[$url_peticion] == "cliente"){
			
				$datos=json_decode(file_get_contents(filename: 'php://input'),true);
				$res=$db_obj->postCliente($datos);
				
				if($res==1){
					
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						\"status':\"ok\",
						\"message':\"Creacion de registro correcto\"
					}";
					
				}else{
					http_response_code(404);
				}
				
			}else if($url_array[$url_peticion] == "producto"){
			
				$datos=json_decode(file_get_contents(filename: 'php://input'),true);
				$res=$db_obj->postProducto($datos);
				
				if($res==1){
					
					header('Content-type: application/json, charset=UTF-8');
					echo "{
						\"status':\"ok\",
						\"message':\"Creacion de registro correcto\"
					}";
					
				}else{
					http_response_code(404);
				}
				
			}else{
				http_response_code(404);
        		echo "sin respuesta";
			}
			
			break;
			
		default:
			http_response_code(404);
			break;
			

	}
}




?>
