<?php
session_start(); // <- Inicia sesión
include('../../Conexion/conexionBD.php');

//Hacemos la funcion
function buscar_user(){
    //Obtenemos los datos de las cajas de texto
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contrasenia'];

    $conn = new conexion();
    $sql = "SELECT * FROM usuarios WHERE usuario='$usuario' AND contraseña='$contraseña'";
    $user_existe = $conn->ejecutarSQL($sql); //Lo que hacemos es asignar un valor a la variable $user_existe para determinar si existe o nó (en la clase de conexionDB manejamos el flujo)

    //Si el valor retornado en la funcion de conexionBD es un entero quiere decir que existe
    if($user_existe>0){
        //Ahora necesitamos obtener el tipo de usuario
        $sql2 = "SELECT * FROM usuarios WHERE usuario='$usuario' AND contraseña='$contraseña'";
        //Obtenemos la consulta de la sentencia
        $resultado = $conn->consultarSQL($sql2);
        //Obtenemos el valor retornable de la consulta (en este caso quiero el tipo de usuario)
        $datos = $resultado->fetch_assoc();
        //Almacenamos lo obtenido de la consulta del campo tipo_usuario
        $tipo_usuario = $datos['tipo_usuario'];

        //Obtenemos los datos del usuario de forma global (se puede interpretar así ya que las variables se disponen a partir de session_star())
        $_SESSION['id_usuario'] = $datos['id_usuario'];
        $_SESSION['nombre'] = $datos['nombre'];
        $_SESSION['apellidos'] = $datos['apellidos'];
        $_SESSION['usuario'] = $datos['usuario'];
        $_SESSION['contraseña'] = $datos['contraseña'];
        $_SESSION['tipo_usuario'] = $datos['tipo_usuario'];
        $_SESSION['email'] = $datos['email'];
        
        //Si el usuario es alumno, obtenemos su grado y grupo
        if($tipo_usuario == "alumno") {
            // Obtenemos el id_grupo del alumno
            $sql3 = "SELECT id_grupo FROM alumnos WHERE id_alumno='{$_SESSION['id_usuario']}'";
            $resultado2 = $conn->consultarSQL($sql3);
            $grupo = $resultado2->fetch_assoc();
            $id_grupo = $grupo['id_grupo'];

            // Obtenemos grado y grupo usando ese id_grupo
            $sql4 = "SELECT grado, grupo FROM grupos WHERE id_grupo = '$id_grupo'";
            $resultado3 = $conn->consultarSQL($sql4);
            $grado = $resultado3->fetch_assoc();

            // Guardamos en sesión
            $_SESSION['grado'] = $grado['grado'];
            $_SESSION['grupo'] = $grado['grupo'];
        }

        if($tipo_usuario=="administrador"){
            //Redireccionamos a la página correspondiente
            header("Location: ../../Privada/Administrador/interfazadmin.php");
            //Salimos de este php
            exit;
        }
        if($tipo_usuario=="profesor" || $tipo_usuario=="profesor_sabatino"){
            //Redireccionamos a la página correspondiente
            header("Location: ../../Privada/Profesor/interfazprofesor.php");
            //Salimos de este php
            exit;
        }
        if($tipo_usuario=="alumno"){
            //Redireccionamos a la página correspondiente
            header("Location: ../../Privada/AlumnoPadre/interfazalumnopadre.php");
            //Salimos de este php
            exit;
        }
    }else{
        //En caso de que el valor retornado no sea un entero entonces determinamos que las credenciales son erroneas
        header ("Location: ../../Publica/login/loginerror.php");
        echo "Usuario o contraseña incorrectos";
    }
}

//Ejecutamos la funcion
buscar_user();

?>