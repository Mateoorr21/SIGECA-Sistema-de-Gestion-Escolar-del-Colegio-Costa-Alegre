<?php 
Class conexion{
	//Variables privadas de la clase para la conexion
	private $host = 'localhost';
	private $usuario = 'root';
	private $password = '';
	private $base = 'sigecaBD'; //Nombre de la bdds

	//publica porque en ella se programaran las sentencia de baja, alta, consulta, etc
	private $rows = array();
	private $conexion;

	//Esta función solo va a devolver un NUMERO OJO
	Public function ejecutarSQL($sentencia){
		//Abrimos conexion con instancia de mysqli que recibe los valores de $host, $usuario, $password, $base
		$this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base);

		//Asignamos a la variable la consulta obtenida
		$resultado = $this->conexion->query($sentencia);

		//Determinamos con affected_rows si la consulta se hizo y la almacenamos en un valor entero
		$resultado = $this->conexion->affected_rows;//Indica un número de filas afectadas

		//Cerramos conexion, se usa -> para acceder a un metodo
		$this->conexion->close();

		return $resultado; //Retorna el número de filas afectadas
	}

	//ESTA FUNCION ESTÁ DESTINADA A RETORNAN LA SENTENCIA SELECT
	public function consultarSQL($sentencia){
		$this->conexion = new mysqli($this->host, $this->usuario, $this->password, $this->base);

		$resultado = $this->conexion->query($sentencia);
		
		//Cerramos conexion, se usa -> para acceder a un metodo
		$this->conexion->close();

		return $resultado; //RETORNA LA CONSULTA
	}
	
}
?>