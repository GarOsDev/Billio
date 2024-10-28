<?php 

/* 
	Archivo encargado de la conexion a BBDD. También donde desarrollo cada una de las funciones encargadas del correcto funcionamiento de la web.
	Divido dichas funciones en 4 grupos ppales: facturas, clientes, productos y funciones personalizadas. Ésta ultima para funcionalidades enfocadas
	a mejorar el dinamismo de la página.
	Para cada seccion, la organizacion seguida es semejante será la misma: consultas select, delete, put(update) y post(insert))
*/

class Facturasdb{

	public $host;
	public $port;
	public $db;
	public $user;
	public $pass;
	public $pdo;

	public function __construct(){
		$this->host="localhost";
		$this->port="3306";
		$this->db="facturas";
		$this->user="root";
		$this->pass="";
		$this->pdo = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->db",$this->user,$this->pass);
	}
	
	// *************************************** FACTURAS ***************************************


	function getFacturas(){
		
		$consulta = $this->pdo->prepare("SELECT prodfac.id_factura, factura.id_factura as id_fac, factura.codigo_factura as codigo, clientes.nombre, prodfac.precio, prodfac.cantidad, factura.fecha_factura, (prodfac.precio * prodfac.cantidad) as total FROM prodfac, factura, clientes WHERE clientes.id_cliente=factura.id_cliente AND prodfac.id_factura = factura.id_factura");
		
		$consulta->execute();

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}
	
	function getFactura($id_fact){
		
		$consulta = $this->pdo->prepare("SELECT productos.nombre, productos.imagen AS imagen_prod, prodfac.precio, prodfac.cantidad, factura.id_factura, factura.codigo_factura, factura.fecha_factura, clientes.nombre AS nombreCliente, clientes.CIF FROM factura JOIN prodfac ON factura.id_factura = prodfac.id_factura JOIN productos ON prodfac.id_producto = productos.id_producto JOIN clientes ON clientes.id_cliente = factura.id_cliente WHERE factura.id_factura = :id");
		
		$array = [
			":id" => $id_fact
		];
		
		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}
	
	function deleteFactura($id_fact){
	
		$consulta = $this->pdo->prepare("DELETE FROM factura WHERE id_factura=:id");
		
		$array = [
			":id" => $id_fact
		];
		
		$res = $consulta->execute($array);

		return $res;
	
	}
	
	function putFactura($id_fact, $datos){
	
		$consulta = $this->pdo->prepare("UPDATE factura INNER JOIN clientes ON factura.id_cliente=clientes.id_cliente SET factura.id_cliente = clientes.id_cliente , factura.codigo_factura=:codfac, factura.fecha_factura=:fecfac WHERE id_factura=:id");
		
		$array = [
			":codfac" => $datos["codigo_factura"],
			":fecfac" => $datos["fecha_factura"],
			":id" => $id_fact
		];
		
		$res = $consulta->execute($array);

		return $res;
	
	}

	function postFactura($datos){

		$insert_process = $this->pdo;
		
		$consulta = $this->pdo->prepare("INSERT INTO factura (id_cliente, codigo_factura, fecha_factura) VALUES ((SELECT id_cliente FROM clientes WHERE id_cliente=:id_cli),:cod_fac,:fecfac)");

		$array = [
			":id_cli" => $datos["id_cliente"],
			":cod_fac" => $datos["codigo_factura"],
			":fecfac" => $datos["fecha_factura"]
		];

		$res = $consulta->execute($array);

		$id_factura = $insert_process->lastInsertId();

		$consulta2 = $this->pdo->prepare("INSERT INTO prodfac (id_producto, id_factura, precio, cantidad) VALUES ((SELECT id_producto FROM productos WHERE id_producto=:id_prod),:id_fac,(SELECT precio FROM productos WHERE id_producto=:id_prod), :cant)");

		$array2 = [
			":id_prod" => $datos["id_producto"],
			":id_fac" => $id_factura,
			":cant" => $datos["cantidad"]
		];

		$res2 = $consulta2->execute($array2);

		return $res2;
	
	}

	// *************************************** CLIENTES ***************************************
	
	function getClientes(){
		
		$consulta = $this->pdo->prepare("SELECT * FROM clientes");
		
		$consulta->execute();

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}

	function getCliente($id_cliente){
		
		$consulta = $this->pdo->prepare("SELECT * FROM clientes C JOIN factura F ON C.id_cliente=F.id_cliente WHERE C.id_cliente=:cli");
		
		$array = [
			":cli" => $id_cliente
		];
		
		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}

	function deleteCliente($id_cli){
	
		$consulta = $this->pdo->prepare("DELETE FROM clientes WHERE id_cliente=:id");
		
		$array = [
			":id" => $id_cli
		];
		
		$res = $consulta->execute($array);

		return $res;
	
	}

	function putCliente($id_cli, $datos){
	
		$consulta = $this->pdo->prepare("UPDATE clientes SET nombre=:nom, CIF=:cif, direccion=:direc, telefono=:tel WHERE id_cliente=:id");
		
		$array = [
			":nom" => $datos["nombre"],
			":cif" => $datos["CIF"],
			":direc" => $datos["direccion"],
			":tel" => $datos["telefono"],
			":id" => $id_cli
		];
		
		$res = $consulta->execute($array);

		return $res;
	
	}

	function postCliente($datos){

		$consulta = $this->pdo->prepare("INSERT INTO clientes(nombre,CIF,direccion,telefono) values (:nom,:CIF,:direc,:tlf)");
		$array = [
			":nom" => $datos["nombre"],
			":CIF" => $datos["CIF"],
			":direc" => $datos["direccion"],
			":tlf" => $datos["telefono"]
		];
		$res=$consulta->execute($array);
		return $res;

	}

	// *************************************** PRODUCTOS ***************************************

	function getProductos(){
		
		$consulta = $this->pdo->prepare("SELECT * FROM productos");
		
		$consulta->execute();

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}

	function getProducto($id_prod){
		
		$consulta = $this->pdo->prepare("SELECT * FROM productos P  WHERE P.id_producto=:prod");
		
		$array = [
			":prod" => $id_prod
		];
		
		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}

	function deleteProducto($id_prod){
	
		$consulta = $this->pdo->prepare("DELETE FROM productos WHERE id_producto=:id");
		
		$array = [
			":id" => $id_prod
		];
		
		$res = $consulta->execute($array);

		return $res;
	
	}

	function putProducto($id_prod, $datos){
	
		$consulta = $this->pdo->prepare("UPDATE productos SET nombre=:nom, precio=:prec, stock=:stk, imagen=:img WHERE id_producto=:id");
		
		$array = [
			":nom" => $datos["nombre"],
			":prec" => $datos["precio"],
			":stk" => $datos["stock"],
			":img" => $datos["imagen"],
			":id" => $id_prod
		];
		
		$res = $consulta->execute($array);

		return $res;
	
	}

	function postProducto($datos){

		$consulta = $this->pdo->prepare("INSERT INTO productos(nombre,precio,stock,imagen) values (:nom,:prec,:stk,:img)");
		$array = [
			":nom" => $datos["nombre"],
			":prec" => $datos["precio"],
			":stk" => $datos["stock"],
			":img" => $datos["imagen"]
		];
		$res=$consulta->execute($array);
		return $res;

	}


	/* *************************************** FUNCIONES PERSONALIZADAS *************************************** */
	
	function getFacturasGral(){

		$consulta = $this->pdo->prepare("SELECT * FROM factura");
		
		$consulta->execute();

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
	}

	function getCliente_SF($id_cliente){
		
		$consulta = $this->pdo->prepare("SELECT * FROM clientes C WHERE C.id_cliente=:cli");
		
		$array = [
			":cli" => $id_cliente
		];
		
		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;
		
	}

	function comprobarCliente($cliente, $cif){

		$consulta = $this->pdo->prepare("SELECT clientes.nombre, clientes.CIF FROM clientes WHERE clientes.nombre=:cli OR clientes.CIF=:cif");

		$array = [
			":cli" => $cliente,
			":cif" => $cif
		];
		
		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;

	}

	function comprobarFactura($codigo){

		$consulta = $this->pdo->prepare("SELECT factura.codigo_factura FROM factura WHERE factura.codigo_factura=:cod");

		$array = [
			":cod" => $codigo,
		];
		
		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;

	}

	function comprobarProducto($nombre){

		$consulta = $this->pdo->prepare("SELECT productos.nombre FROM productos WHERE productos.nombre=:prod");

		$array = [
			":prod" => $nombre,
		];

		$consulta->execute($array);

		$res = $consulta->fetchAll(PDO::FETCH_ASSOC);
		
		return $res;

	}
}

?>



