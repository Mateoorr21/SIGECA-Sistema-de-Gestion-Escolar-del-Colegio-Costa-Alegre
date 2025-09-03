<?php
include('../../Conexion/conexionBD.php');

//Funcion para obtener el id de un nuevo usuario a agregar
function numcuentanuevousuario() {
    $conn = new conexion();
    //Consulta para obtener el numero de cuenta mas alto
    $sql = "SELECT usuario FROM usuarios ORDER BY usuario DESC LIMIT 1";
    $resultado = $conn->consultarSQL($sql);
    $usuario = $resultado->fetch_assoc(); //Convertimos el valor de la consulta en un array asociativo
    return ($usuario['usuario'] + 1); //Retornamos el numero de cuenta del nuevo usuario
}

//Funcion para validar el alta de un nuevo usuario
function validaraltausuario() {
    //Obtenemos el email del nuevo usuario 
    $email = $_POST['email'];

    $conn = new conexion();
    
    //VALIDACION 1. QUE NO SE REPITA EL CORREO ELECTRONICO
    $busqueda = "SELECT email FROM usuarios WHERE email = '$email'";
    //Asignamos el valor de la consulta a resultado
    $resultado = $conn->consultarSQL($busqueda);

    //Si encuentra un valor dentro de resultado quiere decir que ya hay un usuario con ese correo
    if($resultado && $resultado->num_rows > 0) return "Error. Correo ya existente.";
    
    //Obtenemos nombre, apellidos y tipo de usuario
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellido'];
    $tipo_usuario = $_POST['tipo_usuario'];

    //VALIDACION 2. QUE NO SE REPITA EL ALUMNO
    if ($tipo_usuario === "alumno") { //Si es alumno
        $busqueda = "SELECT usuario FROM usuarios 
                     WHERE nombre = '$nombre' AND apellidos = '$apellidos' AND tipo_usuario = 'alumno'";
        $resultado = $conn->consultarSQL($busqueda);
        if ($resultado && $resultado->num_rows > 0) return "Error. Alumno ya registrado.";
    }

    //VALIDACION 3. QUE NO SE REPITA EL PROFESOR
    if ($tipo_usuario === "profesor") { //Si es profesor
        $sql_profesor = "SELECT usuario FROM usuarios 
                     WHERE nombre = '$nombre' AND apellidos = '$apellidos' AND (tipo_usuario = 'profesor' OR tipo_usuario = 'profesor_sabatino')";
        $resultado = $conn->consultarSQL($sql_profesor);
        if ($resultado && $resultado->num_rows > 0) return "Error. Profesor ya registrado.";
    }

    //En caso de que se pasen las 3 validaciones devolvemos true para solicitar confimración
    return true;
}

//Funcion para consultar las asignaturas dadas de alta
function consultarasignaturas(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT id_asignatura, nombre_asignatura, creditos FROM asignaturas ORDER BY nombre_asignatura";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function altausuario() {
    //Obtenemos los datos
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellido'];
    $usuario = $_POST['usuario'] - 1; //Restamos uno para que coincida
    $contraseña = $_POST['contraseña'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $email = $_POST['email'];

    $conn = new conexion();  //conexion a la base de datos
    
    //Seleccionaos el id del usuario actual mas grande
    $sql0 = "SELECT MAX(id_usuario) AS id_usuario FROM usuarios";
    $resultado1 = $conn->consultarSQL($sql0);   
    $filamax = $resultado1->fetch_assoc(); //Convertimos el valor de la consulta en un array asociativo
    $id_max = $filamax['id_usuario']; //Obtenemos el campo de id_usuario que obtuvimos en la consultas

    $sql1 = "INSERT INTO usuarios (id_usuario, nombre, apellidos, usuario, contraseña, tipo_usuario, email)
    VALUES ($id_max + 1, '$nombre', '$apellidos', '$usuario', '$contraseña', '$tipo_usuario', '$email')";
    $conn->ejecutarSQL($sql1); //Ejecutamos la sentencia con el método de la conexionBD
    //$id_max + 1 es el id del usuario recien insertado

    //Si el tipo es alumno
    if($tipo_usuario == "alumno"){
        //Implementamos la matriculación del alumno a clubes sabatinos y a su grupo
        $grupo = $_POST['grupo'];
        $club = $_POST['club'];

        //Hacemos un INSERT para meterlo a alumnos
        $sql3 = "INSERT INTO alumnos VALUE ($id_max + 1,'$grupo','$club')";
        //Enviamos la consulta al método que ejecuta
        $conn->ejecutarSQL($sql3);

        //Hacemos un UPDATE para subir la cantidad de alumnos en su respectivo grupo
        $sql4 = "UPDATE grupos SET alumnos_inscritos = alumnos_inscritos+1 WHERE id_grupo = $grupo";
        //Ejecutamos la sentencia
        $conn->ejecutarSQL($sql4);

        //Generamos registros de calificaciones para todas materias a las que el grupo está inscrito
        //Consulta para obtener todas las asignaturaas a las que el grupo está inscrito
        $sql5 = "SELECT DISTINCT asignaturas.id_asignatura FROM horarios 
                JOIN asignaturas ON asignaturas.id_asignatura = horarios.id_asignatura
                WHERE horarios.id_grupo = '$grupo' ORDER BY asignaturas.nombre_asignatura";
        $materias = $conn->consultarSQL($sql5); //Ejecutamos consulta

        //Para cada asignatura generamos 3 registros en calificaciones
        while($materia = $materias->fetch_assoc()) {
            $id_asignatura = $materia['id_asignatura']; //Obtenemos el id de la asignatura
            // Insertar calificaciones en 0 para cada parcial (1, 2 y 3)
            for ($parcial = 1; $parcial <= 3; $parcial++) {
                $sql_calificacion = "INSERT INTO calificaciones (id_alumno, id_asignatura, parcial, nota)
                                    VALUES ($id_max + 1, '$id_asignatura', '$parcial', 0)";
                $conn->ejecutarSQL($sql_calificacion);
            }
        }
    } 

    return "Alta de usuario exitosa."; //Retornamos un mensaje de éxito
}

//Funcion para buscar un usuario
function buscarusuario() {
    //Obtenemos el no. cuenta del usuario
    $usuario = $_POST['usuario'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta para buscar al usuario
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Si encuentra un valor dentro de resultado la busqueda fue exitosa
    if($resultado && $resultado->num_rows > 0) return $resultado;
    
    //De lo contrario, no se encontró el usuario
    return "Error. Usuario no encontrado"; //Mensaje de error
}

//Funcion para buscar un usuario a eliminar (no puede ser admin)
function buscarusuarioeliminar() {
    //Obtenemos el no. cuenta del usuario
    $usuario = $_POST['usuario'];
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta para buscar al usuario
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Si encuentra un registro verificamos que no sea admin
    if($resultado && $resultado->num_rows> 0) {
        $datos = $resultado->fetch_assoc(); //Obtenemos los datos del usuario
        if($datos['tipo_usuario'] == 'administrador') return "Error. No se puede eliminar administrador"; //Si es admin mensaje de error
        else return $datos; //Si no es admin devolvemos resultado de consulta
    }
    
    //De lo contrario, no se encontró el usuario
    return "Error. Usuario no encontrado"; //Mensaje de error
}

//Funcion para buscar los datos de un alumno
function buscaralumno() {
    //Obtenemos el no. cuenta del usuario
    $usuario = $_POST['usuario'];
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta sql para buscar al usuario
    $sql = "SELECT alumnos.id_grupo, alumnos.esta_sabatinos, grupos.grado, grupos.grupo FROM alumnos
    JOIN usuarios ON alumnos.id_alumno = usuarios.id_usuario 
    JOIN grupos ON alumnos.id_grupo = grupos.id_grupo WHERE usuarios.usuario = '$usuario'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    $datos_alumno = $resultado->fetch_assoc(); //Convertimos el valor de la consulta en un array asociativo
    return $datos_alumno; //Retornamos el id del grupo y el estado de sabatinos
}

//Funcion para consultar los grupos a los que se puede cambiar un alumno
function consultargruposvalidos(){
    //Obtenemos el no. cuenta del usuario
    $usuario = $_POST['usuario'];
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta con subconsutla para obtener los grupos del mismo grado que el alumno
    $sql = "SELECT id_grupo, grado, grupo FROM grupos WHERE grado = (
    SELECT grupos.grado FROM grupos JOIN alumnos ON alumnos.id_grupo = grupos.id_grupo
    JOIN usuarios ON alumnos.id_alumno = usuarios.id_usuario
    WHERE usuarios.usuario = '$usuario') ORDER BY grupo";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para validar modificar usuario
function validarmodificarusuario() {
    //Obtenemos los valores de las cajas de texto
    $id_usuario = $_POST['id_usuario'];
    $usuario = $_POST['usuario'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellido'];
    $contraseña = $_POST['contraseña'];
    $email = $_POST['email'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //VALIDACION 1. QUE SE HAYAN HECHO CAMBIOS
    $sql1 ="SELECT * FROM usuarios WHERE usuario = '$usuario'";
    $resultado1 = $conn->consultarSQL($sql1);
    $fila = $resultado1->fetch_assoc(); //Convertimos el valor de la consulta en un array asociativo

    //Si los datos son iguales a los de la BDDS es porque no se hizo modificación
    if($nombre == $fila['nombre'] && $apellidos == $fila['apellidos'] && $contraseña == $fila['contraseña'] && $email == $fila['email']){
        //Si el tipo de usuario no es alumno, no se modifico el usuario
        if($tipo_usuario != 'alumno') return "Error. Usuario no modificado";

        //Obtenemos los datos modificados del alumno
        $grupo = $_POST['grupo'];
        $club = $_POST['club'];

        //Segunda consulta para ver si se modificaron los datos del alumno
        $sql2 = "SELECT * FROM alumnos WHERE id_alumno = '$id_usuario'";
        $resultado2 = $conn->consultarSQL($sql2);
        $datos_alumno = $resultado2->fetch_assoc(); //Convertimos el valor de la consulta en un array asociativo

        if($datos_alumno['id_grupo'] == $_POST['grupo'] && $datos_alumno['esta_sabatinos'] == $_POST['club']){
            //Si el grupo y el club son iguales a los de la BDDS, no se modifico el usuario
            return "Error. Usuario no modificado";
        }
    }

    //VALIDACION 2. QUE NO SE REPITA EL CORREO ELECTRONICO
    //Buscamos usuario diferentes al modificado con el correo ingresado
    $sql3 = "SELECT * FROM usuarios WHERE email = '$email' AND usuario != '$usuario'";
    $resultado3 = $conn->consultarSQL($sql3); //Ejecutamos la consulta
    //Si encuentra un valor dentro de resultado quiere decir que ya hay un usuario con ese correo
    if($resultado3->num_rows > 0) return "Error. Correo ya existente.";

    //VALIDACION 3. QUE NO SE REPITA EL ALUMNO
    if ($tipo_usuario == "alumno") { //Si es alumno
        $busqueda = "SELECT usuario FROM usuarios 
                     WHERE nombre = '$nombre' AND apellidos = '$apellidos' AND tipo_usuario = 'alumno' AND usuario != '$usuario'";
        $resultado = $conn->consultarSQL($busqueda);
        if ($resultado && $resultado->num_rows > 0) return "Error. Alumno ya registrado.";
    }

    //VALIDACION 4. QUE NO SE REPITA EL PROFESOR
    if ($tipo_usuario == "profesor" || $tipo_usuario == "profesor_sabatino") { //Si es profesor
        $sql_profesor = "SELECT usuario FROM usuarios 
                     WHERE nombre = '$nombre' AND apellidos = '$apellidos' AND (tipo_usuario = 'profesor' OR tipo_usuario = 'profesor_sabatino') AND usuario != '$usuario' ";
        $resultado = $conn->consultarSQL($sql_profesor);
        if ($resultado && $resultado->num_rows > 0) return "Error. Profesor ya registrado.";
    }

    //Si paso estas validaciones retornamos true para solicitar confirmación
    return true;
}

//Funcion para modificar usuario
function modificarusuario() {
    //Obtenemos los valores de las cajas de texto
    $id_usuario = $_POST['id_usuario'];
    $usuario = $_POST['usuario'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellido'];
    $contraseña = $_POST['contraseña'];
    $email = $_POST['email'];

    $conn = new conexion();  //conexion a la base de datos
    //Consulta sql para modificar el usuario
    $sql1 = "UPDATE usuarios SET nombre = '$nombre', apellidos = '$apellidos', contraseña = '$contraseña', email='$email' WHERE usuario = '$usuario'";
    $conn->ejecutarSQL($sql1); //Ejecutamos la sentencia con el método de la conexionBD

    //Modificación adicional para el caso de que el usuario sea un alumno
    if($_POST['tipo_usuario'] == "alumno"){
        //Obtenemos el grupo y el club
        $grupo = $_POST['grupo'];
        $club = $_POST['club'];

        //Consulta sql para obtener el grupo actual del alumno
        $resultado = $conn->consultarSQL("SELECT * FROM alumnos WHERE id_alumno = '$id_usuario'");
        $grupo_actual = $resultado->fetch_assoc(); //Convertimos el valor de la consulta en un array

        //Si se cambio de grupo actualizamos la cantidad de alumnos inscritos en los grupos
        if($grupo_actual['id_grupo'] != $grupo){
            //Consulta sql para actualizar la cantidad de alumnos inscritos en el grupo actual
            $sql2_1 = "UPDATE grupos SET alumnos_inscritos = alumnos_inscritos - 1 WHERE id_grupo = '{$grupo_actual['id_grupo']}'";
            $conn->ejecutarSQL($sql2_1); //Ejecutamos

            //Consulta sql para actualizar la cantidad de alumnos inscritos al nuevo grupo
            $sql2_2 = "UPDATE grupos SET alumnos_inscritos = alumnos_inscritos + 1 WHERE id_grupo = '$grupo'";
            $conn->ejecutarSQL($sql2_2); //Ejecutamos
            
            // Obtener asignaturas actuales del alumno, podemos consultar tabla de calificaciones
            $sql_asignaturas_anteriores = "SELECT DISTINCT id_asignatura FROM calificaciones WHERE id_alumno = '$id_usuario'";
            $asignaturas_anteriores = $conn->consultarSQL($sql_asignaturas_anteriores);

            //Obtenemos las asignaturas del nuevo grupo
            $sql_nuevas_asignaturas = "SELECT DISTINCT asignaturas.id_asignatura FROM horarios 
                    JOIN asignaturas ON asignaturas.id_asignatura = horarios.id_asignatura
                    WHERE horarios.id_grupo = '$grupo' ORDER BY asignaturas.nombre_asignatura";
            $asignaturas_nuevas = $conn->consultarSQL($sql_nuevas_asignaturas); //Ejecutamos consulta

            // Convertimos resultados en arrays simples
            $anteriores = [];
            while ($row = $asignaturas_anteriores->fetch_assoc()) $anteriores[] = $row['id_asignatura'];
            
            $nuevas = [];
            while ($row = $asignaturas_nuevas->fetch_assoc()) $nuevas[] = $row['id_asignatura'];

            // Determinar asignaturas que ya no tendrá (para borrar calificaciones y faltas)
            //Obtenemos las materias anteriores que no están entre las nuevas
            $asignaturas_eliminar = array_diff($anteriores, $nuevas);

            // Determinar asignaturas nuevas que no tenía antes (para insertar calificaciones)
            // Obtenemos las "nuevas materias"
            $asignaturas_insertar = array_diff($nuevas, $anteriores);

            // Eliminar calificaciones y faltas de asignaturas que ya no tendrá
            foreach ($asignaturas_eliminar as $id_asignatura) {
                $sql_borrar_calificaciones = "DELETE FROM calificaciones WHERE id_alumno = '$id_usuario' AND id_asignatura = '$id_asignatura'";
                $conn->ejecutarSQL($sql_borrar_calificaciones); //Ejecutamos consulta

                $sql_borrar_faltas = "DELETE FROM falta WHERE id_alumno = '$id_usuario' AND id_asignatura = '$id_asignatura'";
                $conn->ejecutarSQL($sql_borrar_faltas); //Ejecutamos consulta
            }
            
            // Insertar calificaciones en 0 para nuevas asignaturas
            foreach ($asignaturas_insertar as $id_asignatura) {
                //Un registro para cada paricial
                for ($parcial = 1; $parcial <= 3; $parcial++) {
                    $sql_insertar_calif = "INSERT INTO calificaciones (id_alumno, id_asignatura, parcial, nota)
                                        VALUES ('$id_usuario', '$id_asignatura', '$parcial', 0)";
                    $conn->ejecutarSQL($sql_insertar_calif); //Ejecutamos consulta
                }
            }
        }

        //Consulta sql para modificar el grupo y el club del alumno
        $sql3 = "UPDATE alumnos SET id_grupo = '$grupo', esta_sabatinos = '$club' WHERE id_alumno = '$id_usuario'";
        $conn->ejecutarSQL($sql3); //Ejecutamos la sentencia

        //Si se desinscribe a sabatinos, se resta, se eliminan las asistencias del alumno
        if($club == 0){
            //Consulta sql para eliminar las asistencias del alumno
            $sql4 = "DELETE FROM sabatinos WHERE id_alumno = '$id_usuario'";
            $conn->ejecutarSQL($sql4); //Ejecutamos la sentencia
        }
    }

    return "Modificación de usuario exitosa."; //Retornamos un mensaje de éxito
}   

//Funcion para buscar los datos de los horarios que imparte el profesor
function consultarhorarios(){
    $usuario = $_POST['usuario'];
    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //Obtenemos los horarios que el profesor imparte, ordenados por asignatura, grupo, dia y hora
    $sql = "SELECT horarios.*, asignaturas.nombre_asignatura, grupos.grado, grupos.grupo FROM horarios 
    JOIN usuarios ON usuarios.id_usuario = horarios.id_profesor
    JOIN asignaturas ON asignaturas.id_asignatura = horarios.id_asignatura 
    JOIN grupos ON grupos.id_grupo = horarios.id_grupo WHERE usuarios.usuario = '$usuario' ORDER BY horarios.id_asignatura, horarios.id_grupo,
    FIELD(dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), horarios.horaInicio";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Si no tiene horarios devolvemos un mensaje que lo indca
    if ($resultado->num_rows==0) return "Sin horas";

    //Si el profesor tiene horas, las agrupamos por cada par de asignatura-grupo
    $horariosAgrupados = [];

    // Agrupamos los horarios por asignatura y grupo
    while ($row = $resultado->fetch_assoc()) {
        $clave = $row['nombre_asignatura'] . ' - ' . $row['grado'].$row['grupo'];
        $horariosAgrupados[$clave][] = $row;
    }

    return $horariosAgrupados; //Devolvemos los horarios agrupados
}

//Funcion para consultar los profesores disponibles para dar una materia
function consultarprofesoresdisponibles($id_asignatura, $id_grupo){
    $profesoresDisponibles = []; //Declaramos arreglo con los profesores disponibles
    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //Consulta 1 para obtener todos los horarios correspondientes a esa asignatura y grupo
    $sql1 = "SELECT dia, horaInicio, horaFin FROM horarios 
    WHERE id_asignatura = '$id_asignatura' AND id_grupo = '$id_grupo'";
    $resultado1 = $conn->consultarSQL($sql1); //Ejecutamos la consulta

    // Guardamos los horarios en un arreglo para poder recorrerlos con un foreach
    $horarios = [];
    while ($fila = $resultado1->fetch_assoc()) $horarios[] = $fila; //Agreganos cada registro

    //Consulta 2 para obtener todos los profesores que pueden impartir clase
    $sql2 = "SELECT id_usuario, nombre, apellidos FROM usuarios 
    WHERE tipo_usuario = 'profesor' OR tipo_usuario = 'profesor_sabatino'";
    $resultado2 = $conn->consultarSQL($sql2); //Ejecutamos la consulta

    //Verificamos para cada uno de los profesores si está disponible en los horarios o no
    while ($profesor = $resultado2->fetch_assoc()) {
        $disponible = true;

        //Recorremos cada uno de los horarios
        foreach ($horarios as $horario) {
            //Obtenemos los datos del horario en cuestión
            $dia = $horario['dia'];
            $horaInicio = $horario['horaInicio'];
            $horaFin = $horario['horaFin'];
            $id_profesor = $profesor['id_usuario']; //Obtenemos el id del profesor
            
            //Consulta 3 para obtener si hay algun horario para este profesor en ese día que se traslape con el actual
            $sql3 = "SELECT * FROM horarios WHERE id_profesor = $id_profesor
            AND dia = '$dia' AND horaInicio <= '$horaFin' AND horaFin >= '$horaInicio'";
            $resultado3 = $conn->consultarSQL($sql3); //Ejecutamos la consulta

            //Si hay horarios del profesor que se crucen
            if($resultado3->num_rows>0) {
                $disponible = false; //El profesor no esta disponible
                break; //Salimos del while que recorre los horarios
            }
        }

        //Si después de consultar el profesor está disponible lo añadimos al arreglo
        if ($disponible) $profesoresDisponibles[] = $profesor;
    }

    //Si no hay profesores disponibles devolvemos mensaje
    if(empty($profesoresDisponibles)) echo "No hay disponibles";
    else return $profesoresDisponibles; //De lo contrario devolvemos profesores disponibles
}

//Funcion para dar de baja un usuario
function eliminarusuario(){
    // Obtener datos principales del usuario
    $id_usuario = $_POST['id_usuario'];
    $tipo_usuario = $_POST['tipo_usuario'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //Si es profesor sabatino, cambiamos el profesor encargado de sabatinos
    if($tipo_usuario == 'profesor_sabatino') {
        $nuevo_profesor = $_POST['nuevo_profesor']; //Obtenemos el nuevo profesor encargado de clubes sabatinos
        $conn = new conexion(); //Hacemos la conexión a la base de datos
        //Actualizamos el tipo de usuario del profesor sabatino a profesor
        $sql1 = "UPDATE usuarios SET tipo_usuario = 'profesor' WHERE id_usuario = '$id_usuario'";
        $conn->ejecutarSQL($sql1); //Ejecutamos la consulta
        //Actualizamos el tipo de usuario del nuevo profesor a profesor sabatino
        $sql2 = "UPDATE usuarios SET tipo_usuario = 'profesor_sabatino' WHERE id_usuario = '$nuevo_profesor'";
        $conn->ejecutarSQL($sql2); //Ejecutamos la consulta
    }

    // Si es profesor o profesor sabatino con horarios, modificamos los horarios del profesor
    if (($tipo_usuario == 'profesor' || $tipo_usuario == 'profesor_sabatino') && isset($_POST['horarios_por_asignatura_grupo'], $_POST['reemplazo_para_asignatura_grupo'])) {
        $reemplazos = $_POST['reemplazo_para_asignatura_grupo'];   // array de id_usuario (reemplazos)
        $horarios = $_POST['horarios_por_asignatura_grupo']; //array con todos los horarios de todas las materias

        //Recorremos cada uno de los reemplazos para cada grupo-asignatura
        foreach ($reemplazos as $clave => $id_reemplazo) {

            //Recorremos cada uno de los horarios para cada grupo-asignatura
            foreach ($horarios[$clave] as $id_horario) {

                // Consulta sql para actualizar el horario con el reemplazo
                $sql3 = "UPDATE horarios SET id_profesor = '$id_reemplazo' WHERE id_horario = '$id_horario'";
                $conn->ejecutarSQL($sql3); //Ejecutamos la consulta
            }
        }
    }

    // Si es alumno restamos uno al grupo en el que estaba
    if($tipo_usuario == 'alumno') {
        $grupo = $_POST['id_grupo']; //Obtenemos grupo
        //Hacemos un UPDATE para bajar la cantidad de alumnos en su respectivo grupo
        $sql4 = "UPDATE grupos SET alumnos_inscritos = alumnos_inscritos-1 WHERE id_grupo = $grupo";
        $conn->ejecutarSQL($sql4); //Ejecutamos la sentencia
    }

    //Finalmente eliminamos usuario de la base de datos
    $sql5 = "DELETE FROM usuarios WHERE id_usuario = '$id_usuario'";
    $conn->ejecutarSQL($sql5); //Ejecutamos la consulta

    //Si es profesor con horas mensaje de reasignación exitosa
    if (($tipo_usuario == 'profesor' || $tipo_usuario == 'profesor_sabatino') && isset($_POST['horario'], $_POST['reemplazo'])) {
        return "Eliminación de profesor y reasignación de horarios exitosa.";
    }

    else return "Eliminación de usuario exitosa."; //Si no lo es solo mensaje con baja exitosa

}

//Funcion para validar el alta de una nueva asignatura
function validaraltamateria(){
    //Obtenemos el nombre de la asignatura
    $nombre = $_POST['nombre'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //VALIDACION 1. QUE NO SE REPITA EL NOMBRE DE LA ASIGNATURA
    $sql = "SELECT nombre_asignatura FROM asignaturas WHERE nombre_asignatura = '$nombre'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Si encuentra un valor dentro de resultado quiere decir que ya hay una asignatura con ese nombre
    if($resultado && $resultado->num_rows > 0) return "Error. Asignatura ya existente.";
    
    return true; //Si no existe, retornamos true para solicitar confirmación
}

//Funcion para dar de alta una nueva asignatura
function altamateria() {
    //Obtenemos los datos
    $nombre = $_POST['nombre'];
    $creditos = $_POST['creditos'];

    $conn = new conexion();  //conexion a la base de datos
    $sql1 = "INSERT INTO asignaturas (nombre_asignatura, creditos) VALUES ('$nombre', '$creditos')";
    $conn->ejecutarSQL($sql1); //Ejecutamos la sentencia con el método de la conexionBD

    return "Alta de asignatura exitosa."; //Retornamos un mensaje de éxito
}

//Funcion para buscar los datos de una asignatura
function buscarasignatura() {
    //Obtenemos el nombre de la asignatura
    $nombre_asignatura = $_POST['nombre_asignatura'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta para buscar la asignatura
    $sql = "SELECT * FROM asignaturas WHERE nombre_asignatura = '$nombre_asignatura'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Si encuentra un valor dentro de resultado la busqueda fue exitosa
    if($resultado && $resultado->num_rows > 0) return $resultado;
    
    //De lo contrario, no se encontró la asignatura
    return "Error. Asignatura no encontrada"; //Mensaje de error
}

//Funcion para validar la modificación de una asignatura
function validarmodificarasignatura() {
    //Obtenemos los valores de las cajas de texto
    $id_asignatura = $_POST['id_asignatura'];
    $nombre_asignatura = $_POST['nombre_asignatura'];
    $creditos = $_POST['creditos'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //VALIDACION 1. QUE SE HAYAN HECHO CAMBIOS
    $sql1 ="SELECT * FROM asignaturas WHERE id_asignatura = '$id_asignatura'";
    $resultado1 = $conn->consultarSQL($sql1);
    $fila = $resultado1->fetch_assoc(); //Convertimos el valor de la consulta en un array asociativo

    //Si los datos son iguales a los de la BDDS es porque no se hizo modificación
    if($nombre_asignatura == $fila['nombre_asignatura'] && $creditos == $fila['creditos']){
        return "Error. Asignatura no modificada"; //Retornamos un mensaje de error
    }

    //VALIDACION 2. QUE NO SE REPITA EL NOMBRE DE LA ASIGNATURa
    //Buscamos asignaturas diferentes al modificado con el nombre ingresado
    $sql2 = "SELECT * FROM asignaturas WHERE nombre_asignatura = '$nombre_asignatura' AND id_asignatura != '$id_asignatura'";
    $resultado2 = $conn->consultarSQL($sql2); //Ejecutamos la consulta

     //Si encuentra un valor dentro de resultado quiere decir que ya hay una asignatura con ese nombre
    if($resultado2->num_rows > 0) return "Error. Asignatura ya existente.";

    //Si paso estas validaciones retornamos true para solicitar confirmación
    return true;
}

//Funcion para modificar una asignatura
function modificarasignatura() {
    //Obtenemos los valores de las cajas de texto
    $id_asignatura = $_POST['id_asignatura'];
    $nombre_asignatura = $_POST['nombre_asignatura'];
    $creditos = $_POST['creditos'];

    $conn = new conexion();  //conexion a la base de datos
    //Consulta sql para modificar la asignatura
    $sql1 = "UPDATE asignaturas SET nombre_asignatura = '$nombre_asignatura', creditos = '$creditos' WHERE id_asignatura = '$id_asignatura'";
    $conn->ejecutarSQL($sql1); //Ejecutamos la sentencia con el método de la conexionBD

    return "Modificación de asignatura exitosa."; //Mensaje de éxito
}

//Funcion para eliminar una asignatura
function eliminarasignatura() {
    //Obtenemos la asignatura a eliminar
    $id_asignatura = $_POST['id_asignatura'];

    $conn = new conexion();  //conexion a la base de datos
    //Consulta sql para eliminar asignatura
    $sql1 = "DELETE FROM asignaturas WHERE id_asignatura = '$id_asignatura'";
    $conn->ejecutarSQL($sql1); //Ejecutamos la sentencia 

    return "Eliminación de asignatura exitosa.";
}

//Funcion para validar el alta de un nuevo grupo
function validaraltagrupo() {
    //Obtenemos el grado y grupo
    $grado = $_POST['grado'];
    $grupo = $_POST['grupo'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //VALIDACION 1. QUE EL GRUPO NO EXISTA
    $sql = "SELECT grado, grupo FROM grupos WHERE grado = '$grado' AND grupo = '$grupo'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Si encuentra un valor dentro de resultado quiere decir que ya hay un grupo con ese nombre
    if($resultado && $resultado->num_rows > 0) return "Error. Grupo ya existente.";
    
    return true; //Si no existe, retornamos true para solicitar confirmación
}

//Funcion para dar de alta a un nuevo grupo
function altagrupo() {
    //Obtenemos los datos
    $grado = $_POST['grado'];
    $grupo = $_POST['grupo'];
    $tutor = $_POST['tutor'];

    $conn = new conexion();  //conexion a la base de datos
    //Consulta sql para dar de alta el grupo
    $sql = "INSERT INTO grupos (grado, grupo, id_tutor, alumnos_inscritos) VALUES ('$grado', '$grupo', '$tutor', 0)";
    $conn->ejecutarSQL($sql); //Ejecutamos la sentencia con el método de la conexionBD

    return "Alta de grupo exitosa."; //Retornamos un mensaje de éxito
}

//Funcion para buscar un grupo
function buscargrupo() {
    //Obtenemos el grupo a eliminar
    $id_grupo = $_POST['grupo'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta para buscar el grupo
    $sql = "SELECT grupos.*, usuarios.nombre, usuarios.apellidos FROM grupos 
    JOIN usuarios ON grupos.id_tutor = usuarios.id_usuario WHERE id_grupo = '$id_grupo'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para eliminar un grupo
function eliminargrupo() {
    //Obtenemos el grupo a eliminar
    $id_grupo = $_POST['id_grupo'];

    $conn = new conexion();  //conexion a la base de datos

    // 1. Eliminar usuarios de alumnos de ese grupo
    $sql1 = "DELETE FROM usuarios 
    WHERE id_usuario IN (SELECT id_alumno FROM alumnos WHERE id_grupo = '$id_grupo')";
    $conn->ejecutarSQL($sql1); //Eliminar usuarios primero

    // 2. Consulta sql para eliminar asignatura
    $sql2 = "DELETE FROM grupos WHERE id_grupo = '$id_grupo'";
    $conn->ejecutarSQL($sql2); //Ejecutamos la sentencia 

    return "Eliminación de grupo exitosa.";
}

//Funcion para consultar los tutores disponibles
function consultartutores(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT * FROM usuarios WHERE (tipo_usuario = 'profesor' OR tipo_usuario = 'profesor_sabatino')
    AND id_usuario NOT IN (SELECT id_tutor FROM grupos WHERE id_tutor IS NOT NULL ) ORDER BY usuarios.apellidos;"; //Consulta para obtener los tutores
    //Se seleccionan los profesores cuyo id no se encuentre como tutor en la tabla grupos
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar los grupos dados de alta
function consultargrupos(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT id_grupo, grado, grupo FROM grupos ORDER BY grado, grupo"; //Consulta para obtener los grupos
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar los profesores disponibles para impartir clases
function consultarprofesores(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT id_usuario, nombre, apellidos FROM usuarios WHERE tipo_usuario = 'profesor' OR tipo_usuario = 'profesor_sabatino' ORDER BY apellidos";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar el id y el nombre completo del profesor encargado de clubes sabatinos
function consultarprofesorsabatino(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos FROM usuarios WHERE usuarios.tipo_usuario = 'profesor_sabatino'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    $profesor_sabatino = $resultado->fetch_assoc();
    $nombre_completo = $profesor_sabatino['nombre'] . " " . $profesor_sabatino['apellidos'];
    $_SESSION['id_profesor_sabatino'] = $profesor_sabatino['id_usuario']; //Guardamos el id del profesor sabatino en la sesión
    return $nombre_completo; //Retornamos el nombre completo del profesor sabatino
}

//Funcion para consultar los profesores disponibles para impartir clubes sabatinos
function consultarprofesoresposibles(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT id_usuario, nombre, apellidos FROM usuarios WHERE tipo_usuario = 'profesor' ORDER BY apellidos";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    //Convertimos en arreglo
    $lista_profesores = [];
    while ($fila = $resultado->fetch_assoc()) {
        $lista_profesores[] = $fila; //Añadimos cada registro al arreglo
    }
    return $lista_profesores; //Retornamos el resultado de la consulta
}

//Funcion para actualizar el profesor encargado de clubes sabatinos
function gestionarsabatino(){
    $id_profesor_sabatino = $_POST['id_profesor_sabatino']; //Obtenemos el id del profesor sabatino
    $nuevo_profesor = $_POST['nuevo_profesor']; //Obtenemos el nuevo profesor encargado de clubes sabatinos
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Actualizamos el tipo de usuario del profesor sabatino a profesor
    $sql = "UPDATE usuarios SET tipo_usuario = 'profesor' WHERE id_usuario = '$id_profesor_sabatino'";
    $conn->ejecutarSQL($sql); //Ejecutamos la consulta
    //Actualizamos el tipo de usuario del nuevo profesor a profesor sabatino
    $sql2 = "UPDATE usuarios SET tipo_usuario = 'profesor_sabatino' WHERE id_usuario = '$nuevo_profesor'";
    $conn->ejecutarSQL($sql2); //Ejecutamos la consulta
    return "Encargado de clubes sabatinos actualizado.";
}

//Funcion para consultar los justificantes cuyo estado sea "pendiente"
function consultarjustificantes(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT usuarios.nombre, usuarios.apellidos, grupos.grado, grupos.grupo, 
    justificantes.id_justificante, justificantes.fecha_justificar, justificantes.estado, justificantes.motivo 
    FROM justificantes JOIN usuarios ON usuarios.id_usuario = justificantes.id_alumno
    JOIN alumnos ON usuarios.id_usuario = alumnos.id_alumno JOIN grupos ON alumnos.id_grupo = grupos.id_grupo
    WHERE justificantes.estado = 'pendiente' ORDER BY justificantes.fecha_justificar DESC";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para gestionar el justificante
function gestionarjustificante(){
    $id_justificante = $_POST['id_justificante']; //Obtenemos el id del justificante
    $accion = $_POST['Accion']; //Obtenemos la acción a realizar (aceptar o rechazar)
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    if($accion == "aprobar"){
        //Actualizamos el estado del justificante a "aceptado"
        $sql = "UPDATE justificantes SET estado = 'aprobado' WHERE id_justificante = '$id_justificante'";
        $conn->ejecutarSQL($sql); //Ejecutamos la consulta
        return "Justificante aprobado exitosamente.";
    }
    
    else{
        //Actualizamos el estado del justificante a "rechazado"
        $sql = "UPDATE justificantes SET estado = 'rechazado' WHERE id_justificante = '$id_justificante'";
        $conn->ejecutarSQL($sql); //Ejecutamos la consulta
        return "Justificante rechazado exitosamente.";
    }
}

//Funcion para validar la accion de matricular un grupo a una asignatura
function validarmatriculargrupo(){
    $id_grupo = $_POST['grupo']; //Obtenemos el id del grupo
    $id_asignatura = $_POST['asignatura']; //Obtenemos el id de la asignatura
    $id_profesor = $_POST['profesor']; //Obtenemos el id del grupo
    
    //VALIDACION 0. QUE NO HAYA CAMPOS VACÍOS (HORARIOS)
    if(!isset($_POST['dia'])){
        return "Error: Debe ingresar al menos un horario.";
    }

    $dias = $_POST['dia']; // Array con los días
    $horas_inicio = $_POST['hora_inicio']; // Array con horas de inicio
    $horas_fin = $_POST['hora_fin']; // Array con horas de fin

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    //VALIDACION 1. QUE LA HORA DE INICIO Y DE FIN SEAN VALIDAS
    //Para cada uno de los horarios ingresados por el adminsitrador
    for ($i = 0; $i < count($dias); $i++) {
        //Obtenemos la hora de inicio y fin
        $hora_inicio = $horas_inicio[$i];
        $hora_fin = $horas_fin[$i];

        // Convertimos las horas a segundos para comparar fácilmente
        $inicio_nuevo = strtotime($hora_inicio);
        $fin_nuevo = strtotime($hora_fin);

        if($fin_nuevo <= $inicio_nuevo){
            return "En horario ".($i+1)." el horario de inicio debe ser menor que el de fin.";
        }
    }

    //VALIDACION 2. QUE LOS HORARIOS INGRESADOS NO SE CRUCEN UNO CON OTRO
    //Para cada uno de los horarios ingresados por el administrador, menos el ultimo
    for ($i = 0; $i < count($dias) - 1; $i++) {
        //Obtenemos el dia y las horas de inicio y fin en segundos
        $dia_actual = $dias[$i];
        $inicio_actual = strtotime($horas_inicio[$i]);
        $fin_actual = strtotime($horas_fin[$i]);

        //Comparamos con los siguientes horarios
        for ($j = $i + 1; $j < count($dias); $j++) {
            // Solo comparamos si es el mismo día
            if ($dias[$j] == $dia_actual) {
                $inicio_comparar = strtotime($horas_inicio[$j]);
                $fin_comparar = strtotime($horas_fin[$j]);

                // Verificamos si hay cruce de horarios
                if ($inicio_actual <= $fin_comparar && $fin_actual >= $inicio_comparar) {
                    return "Error. Conflicto entre horario " . ($i + 1) . " y horario " . ($j + 1) . " en el día $dia_actual.";
                }
            }
        }
    }

    //VALIDACION 3. QUE EL GRUPO NO ESTE YA MATRICULADO A LA ASIGNATURA
    //Consulta para verificar si el grupo ya está matriculado a la asignatura
    $sql1 = "SELECT * FROM horarios WHERE id_grupo = '$id_grupo' AND id_asignatura = '$id_asignatura'";
    $resultado1 = $conn->consultarSQL($sql1); //Ejecutamos la consulta

    //Si el grupo ya está matriculado a la asignatura, retornamos un mensaje
    if($resultado1->num_rows > 0){
        return "El grupo ya está matriculado a la asignatura.";
    }

    //VALIDACION 4. QUE LOS CREDITOS INGRESADOS NO EXCEDAN LOS SEMANALES
    //Consulta para obtener los creditos de la asignatura seleccionada
    $sql2 = "SELECT creditos FROM asignaturas WHERE id_asignatura = '$id_asignatura'";
    $resultado2 = $conn->consultarSQL($sql2); //Ejecutamos la consulta
    $fila2 = $resultado2->fetch_assoc(); //Comvertimos el valor de la consulta en un array asociativo
    $creditos_semanales = $fila2['creditos']; //Obtenemos el campo de creditos que obtuvimos en la consultas

    // Calculamos total de minutos ingresados
    $minutos_totales = 0;
    for ($i = 0; $i < count($dias); $i++) {
        //Obtenemos en segundos las horas de inicio y fin
        $hora_inicio = strtotime($horas_inicio[$i]);
        $hora_fin = strtotime($horas_fin[$i]);
        $duracion = ($hora_fin - $hora_inicio) / 60; // dividimos entre 60 para duración en minutos
        $minutos_totales += $duracion; //sumamos la duracion en minutos
    }

    //Calculamos los creditos ingresados (1 credito = 50 minutos)
    $creditos_ingresados = round($minutos_totales / 50, 2);

    //Comparamos los creditos ingresados con los creditos semanales
    //Mostramos mensaje de error si faltan o sobran creditos
    if ($creditos_ingresados < $creditos_semanales) {
        return "Faltan horarios. Ingresaste $creditos_ingresados créditos de $creditos_semanales requeridos.";
    } elseif ($creditos_ingresados > $creditos_semanales) {
        return "Hay horarios de más. Ingresaste $creditos_ingresados créditos de $creditos_semanales permitidos.";
    } 
        
    //VALIDACION 5. QUE EL HORARIO PARA EL GRUPO NO SE CRUCE CON OTRO
    //Para cada uno de los horarios ingresados por el administrador
    for ($i = 0; $i < count($dias); $i++) {
        //Obtenemos el dia y las horas de inicio y fin
        $dia = $dias[$i];
        $hora_inicio = $horas_inicio[$i];
        $hora_fin = $horas_fin[$i];

        // Convertimos las horas a segundos para comparar fácilmente
        $inicio_nuevo = strtotime($hora_inicio);
        $fin_nuevo = strtotime($hora_fin);

        // Consulta para obtener los horarios ya registrados para ese grupo en el mismo día
        $sql_horarios = "SELECT horaInicio, horaFin FROM horarios 
                        WHERE id_grupo = '$id_grupo' AND dia = '$dia'";
        $horarios_existentes = $conn->consultarSQL($sql_horarios);
        
        //Recorremos los horarios existentes para ver si alguna se cruza con el ingresado
        while ($horario_existente = $horarios_existentes->fetch_assoc()) {
            //Obtenemos las horas de inicio y fin de los horarios existentes en segundos
            $inicio_existente = strtotime($horario_existente['horaInicio']);
            $fin_existente = strtotime($horario_existente['horaFin']);

            // Validamos si hay cruce de horarios
            // Si el inicio del nuevo horario es menor que el fin del existente 
            // y el fin del nuevo horario es mayor que el inicio del existente
            if ($inicio_nuevo <= $fin_existente && $fin_nuevo >= $inicio_existente) {
                return "Error: El horario del día $dia de $hora_inicio a $hora_fin se cruza con otro del grupo.";
            }
        }
    }

    //VALIDACION 6. QUE EL PROFESOR NO TENGA OTRO HORARIO QUE SE CRUCE CON EL NUEVO
    for ($i = 0; $i < count($dias); $i++) {
        //Obtenemos el dia y las horas de inicio y fin
        $dia = $dias[$i];
        $hora_inicio = $horas_inicio[$i];
        $hora_fin = $horas_fin[$i];

        // Convertimos las horas a segundos para comparar fácilmente
        $inicio_nuevo = strtotime($hora_inicio);
        $fin_nuevo = strtotime($hora_fin);

        // Consulta para obtener los horarios que ya tiene el profesor en ese mismo día
        $sql_horarios_profesor = "SELECT horaInicio, horaFin FROM horarios 
                                WHERE id_profesor = '$id_profesor' AND dia = '$dia'";
        $horarios_profesor = $conn->consultarSQL($sql_horarios_profesor);

        // Recorremos los horarios del profesor para validar que no se cruce con los nuevos
        while ($horario = $horarios_profesor->fetch_assoc()) {
            $inicio_existente = strtotime($horario['horaInicio']);
            $fin_existente = strtotime($horario['horaFin']);
            
            // Validamos si hay cruce de horarios
            if ($inicio_nuevo <= $fin_existente && $fin_nuevo >= $inicio_existente) {
                // Si hay cruce, retornamos un mensaje de error, damos formato a la hora de
                // inicio y fin ya existente para mostrarla al administrador
                return "Error: El profesor ya tiene una clase el día $dia de " .
                    date('H:i', $inicio_existente) . " a " . date('H:i', $fin_existente) .
                    ".";
            }
        }
    }

    return true; // Si pasa todas las validaciones, retornamos true para solicitar confirmación
}

//Funcion para matricular un grupo a una asignatura
function matriculargrupo(){
    $id_grupo = $_POST['grupo']; //Obtenemos el id del grupo
    $id_asignatura = $_POST['asignatura']; //Obtenemos el id de la asignatura
    $id_profesor = $_POST['profesor']; //Obtenemos el id del grupo

    $dias = $_POST['dia']; // Array con los días
    $horas_inicio = $_POST['hora_inicio']; // Array con horas de inicio
    $horas_fin = $_POST['hora_fin']; // Array con horas de fin

    $conn = new conexion(); //Hacemos la conexión a la base de datos

    // Insertamos los horarios en la base de datos
    for ($i = 0; $i < count($dias); $i++) {
        $dia = $dias[$i];
        $hora_inicio = $horas_inicio[$i];
        $hora_fin = $horas_fin[$i];

        //Consulta para insertar el horario a la base de datos
        $sql_insertar = "INSERT INTO horarios (dia, horaInicio, horaFin, id_asignatura, id_grupo, id_profesor)
                        VALUES ('$dia', '$hora_inicio', '$hora_fin', '$id_asignatura', '$id_grupo', '$id_profesor')";
        $resultado = $conn->ejecutarSQL($sql_insertar); //Ejecutamos la cosulta
    }

    // Obtener los alumnos del grupo en el que se matriculó
    $sql_alumnos = "SELECT id_alumno FROM alumnos WHERE id_grupo = '$id_grupo'";
    $result_alumnos = $conn->consultarSQL($sql_alumnos);

    while ($alumno = $result_alumnos->fetch_assoc()) {
        $id_alumno = $alumno['id_alumno'];

        // Insertar calificaciones en 0 para cada parcial (1, 2 y 3)
        for ($parcial = 1; $parcial <= 3; $parcial++) {
            $sql_calificacion = "INSERT INTO calificaciones (id_alumno, id_asignatura, parcial, nota)
                                 VALUES ('$id_alumno', '$id_asignatura', '$parcial', 0)";
            $conn->ejecutarSQL($sql_calificacion);
        }
    }

    return "El grupo fue matriculado correctamente a la asignatura.";
}
?>