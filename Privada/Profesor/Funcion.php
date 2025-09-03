<?php
include('../../Conexion/conexionBD.php');
if (session_status() === PHP_SESSION_NONE) session_start();

function consultamaterias(){
    //Obtenemos el id del profesor
    $idprofesor = $_POST['id_profesor'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT DISTINCT asignaturas.id_asignatura, asignaturas.nombre_asignatura FROM asignaturas JOIN horarios
    ON asignaturas.id_asignatura = horarios.id_asignatura WHERE horarios.id_profesor = '$idprofesor'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function consultagrupos(){
    //Obtenemos el id del profesor
    $idprofesor = $_POST['id_profesor'];
    $idasignatura = $_POST['materia']; //Obtenemos el id de la materia seleccionada

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT DISTINCT grupos.id_grupo, grupos.grado, grupos.grupo FROM grupos JOIN horarios
    ON grupos.id_grupo = horarios.id_grupo WHERE horarios.id_profesor = '$idprofesor' AND horarios.id_asignatura = '$idasignatura'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function consultaralumnos(){
    //Obtenemos el id del grupo
    $idgrupo = $_POST['grupo'];

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos FROM usuarios JOIN alumnos
    ON alumnos.id_alumno = usuarios.id_usuario WHERE alumnos.id_grupo = '$idgrupo' ORDER BY usuarios.apellidos";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function consultarprofesores(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT id_usuario, nombre, apellidos FROM usuarios WHERE tipo_usuario = 'profesor' OR tipo_usuario = 'profesor_sabatino' ORDER BY apellidos";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function consultaralumnosfaltas($idmateria){
    //Obtenemos el id del grupo
    $idgrupo = $_POST['grupo'];
    
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta para obtener los alumnos y el número de faltas
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos, (SELECT COUNT(*)
        FROM falta  WHERE falta.id_alumno = alumnos.id_alumno AND falta.id_asignatura = '$idmateria' AND falta.justificada = 0) AS num_faltas
        FROM usuarios JOIN alumnos ON alumnos.id_alumno = usuarios.id_usuario
        WHERE alumnos.id_grupo = '$idgrupo' ORDER BY usuarios.apellidos";

    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}


function asignarfalta($id_alumno, $id_asignatura, $fecha) {

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    
    //Consulta para verificar si ya existe una falta registrada para el alumno y la fecha
    $sql1 = "SELECT COUNT(*) FROM falta WHERE id_alumno = '$id_alumno' 
    AND id_asignatura = '$id_asignatura' AND fecha_falta = '$fecha'";

    $comprobacion = $conn->consultarSQL($sql1); //Ejecutamos la consulta
    $fila = $comprobacion->fetch_assoc(); //Obtenemos el resultado de la consulta como una fila

    if($fila['COUNT(*)'] > 0) { //Si ya existe la falta devolvemos 0 y termina la función
        return "Ya existe una falta registrada para el alumno en esta fecha."; //Mensaje de error
    }

    //Consulta para insertar la falta en la base de datos
    $sql2 = "INSERT INTO falta (id_alumno, id_asignatura, fecha_falta) VALUES ('$id_alumno', '$id_asignatura', '$fecha')";
    $resultado = $conn->ejecutarSQL($sql2); //Ejecutamos la consulta
    return "Falta registrada exitosamente."; //Retornamos mensaje de exito si se inserta correctamente
}

function consultarfaltasporalumno($idmateria){
    //Obtenemos el id del grupo
    $idgrupo = $_POST['grupo'];
    
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    // Consulta para obtener las fechas de faltas no justificadas por alumno
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos, falta.id_falta, falta.fecha_falta
        FROM usuarios JOIN alumnos ON alumnos.id_alumno = usuarios.id_usuario
        JOIN falta ON falta.id_alumno = alumnos.id_alumno WHERE alumnos.id_grupo = '$idgrupo' 
        AND falta.id_asignatura = '$idmateria' AND falta.justificada = 0 ORDER BY usuarios.apellidos, falta.fecha_falta";

    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function justificarfalta($id_falta){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    // Consulta para justificar una falta de un alumno
    $sql = "UPDATE falta SET justificada = 1 WHERE id_falta = '$id_falta'";
    $resultado = $conn->ejecutarSQL($sql); //Ejecutamos la consulta
    return "Falta justificada exitosamente."; //Retornamos mensaje de exito si se justifica correctamente
}

function consultaralumnossabatinos(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos FROM usuarios 
    JOIN alumnos ON alumnos.id_alumno = usuarios.id_usuario WHERE alumnos.esta_sabatinos = 1 ORDER BY apellidos";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

function registrarasistenciasabatino($id_alumno, $fecha) {
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta para verificar si ya existe una falta registrada para el alumno y la fecha
    $sql1 = "SELECT COUNT(*) FROM sabatinos WHERE id_alumno = '$id_alumno' 
    AND fecha_asistencia = '$fecha'";
    $comprobacion = $conn->consultarSQL($sql1); //Ejecutamos la consulta
    $fila = $comprobacion->fetch_assoc(); //Obtenemos el resultado de la consulta como una fila
    if($fila['COUNT(*)'] > 0) { //Si ya existe la asistencia devolvemos 0 y termina la función
        return "Ya existe una asistencia registrada para el alumno en esta fecha."; //Mensaje de error
    }
    //Consulta para insertar la falta en la base de datos
    $sql2 = "INSERT INTO sabatinos (id_alumno, fecha_asistencia) VALUES ('$id_alumno','$fecha')";
    $resultado = $conn->ejecutarSQL($sql2); //Ejecutamos la consulta
    return "Asistencia registrada exitosamente."; //Retornamos mensaje de exito si se inserta correctamente
}

function consultarcalificacionesporalumno($idmateria){
    //Obtenemos el id del grupo
    $idgrupo = $_POST['grupo'];
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    // Consulta para obtener las calificaciones de los alumnos en la asignatura seleccionada
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos, calificaciones.parcial, calificaciones.nota
        FROM usuarios JOIN alumnos ON alumnos.id_alumno = usuarios.id_usuario
        JOIN calificaciones ON calificaciones.id_alumno = alumnos.id_alumno WHERE alumnos.id_grupo = '$idgrupo' 
        AND calificaciones.id_asignatura = '$idmateria' ORDER BY usuarios.apellidos, calificaciones.parcial";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    // Crear un array para agrupar las calificaciones por alumno usando el ID como clave
    $alumnosAgrupados = [];

    //Recorremos todos los registros de calificaciones
    while ($registro = $resultado->fetch_assoc()) {
        $id = $registro['id_usuario']; //Obtenemos el ID

        // Si aún no se ha agregado el alumno al array, lo hacemos
        if (!isset($alumnosAgrupados[$id])) {
            //Agregamos el alumno con su nombre, apellidos y califiaciones vacías
            $alumnosAgrupados[$id] = [
                'nombre' => $registro['nombre'],
                'apellidos' => $registro['apellidos'],
                'parcial_1' => '',
                'parcial_2' => '',
                'parcial_3' => '',
                'promedio' => 0
            ];
        }

        $parcial = $registro['parcial']; //Obtenemos el número de parcial
        $nota = $registro['nota']; //Obtenemos la nota
                        
        // Guardamos la nota en el arreglo según el número de parcial
        if ($parcial == 1) {
            $alumnosAgrupados[$id]['parcial_1'] = $nota;
        } elseif ($parcial == 2) {
            $alumnosAgrupados[$id]['parcial_2'] = $nota;
        } elseif ($parcial == 3) {
            $alumnosAgrupados[$id]['parcial_3'] = $nota;

            //Si las tres calificaciones ya están asignadas, calculamos el promedio
            //Verificamos que las calificaciones no sean cero
            if($alumnosAgrupados[$id]['parcial_1'] != '0' 
                && $alumnosAgrupados[$id]['parcial_2'] != '0' 
                && $alumnosAgrupados[$id]['parcial_3'] != '0') {
                // Calculamos el promedio de las calificaciones
                $promedio = (
                    $alumnosAgrupados[$id]['parcial_1'] + 
                    $alumnosAgrupados[$id]['parcial_2'] + 
                    $alumnosAgrupados[$id]['parcial_3']) / 3;
                $alumnosAgrupados[$id]['promedio'] = round($promedio, 2); //Redondeamos a 2 decimales
            }
        }
    }

    return $alumnosAgrupados; //Retornamos los alumnos agrupados
}

function asignarcalificaciones($idusuario, $idmateria, $parcial1, $parcial2, $parcial3) {
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    // Primer consulta para actualizar la calificación del primer parcial
    $sql1 = "UPDATE calificaciones SET nota = $parcial1 WHERE id_alumno = '$idusuario' AND id_asignatura = '$idmateria' AND parcial = 1";

    // Segunda consulta para actualizar la calificación del segundo parcial
    $sql2 = "UPDATE calificaciones SET nota = $parcial2 WHERE id_alumno = '$idusuario' AND id_asignatura = '$idmateria' AND parcial = 2";

    // Tercer consulta para actualizar la calificación del tercer parcial
    $sql3 = "UPDATE calificaciones SET nota = $parcial3 WHERE id_alumno = '$idusuario' AND id_asignatura = '$idmateria' AND parcial = 3";

    // Ejecutamos las consultas
    $resultado1 = $conn->ejecutarSQL($sql1); //Ejecutamos la consulta
    $resultado2 = $conn->ejecutarSQL($sql2); //Ejecutamos la consulta
    $resultado3 = $conn->ejecutarSQL($sql3); //Ejecutamos la consulta
    return "Calificaciones asignadas exitosamente."; //Retornamos mensaje de exito si se actualizan correctamente
}

function modificarcalificaciones($idusuario, $idmateria, $parcial1, $parcial2, $parcial3) {
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    // Primer consulta para actualizar la calificación del primer parcial
    $sql1 = "UPDATE calificaciones SET nota = $parcial1 WHERE id_alumno = '$idusuario' AND id_asignatura = '$idmateria' AND parcial = 1";

    // Segunda consulta para actualizar la calificación del segundo parcial
    $sql2 = "UPDATE calificaciones SET nota = $parcial2 WHERE id_alumno = '$idusuario' AND id_asignatura = '$idmateria' AND parcial = 2";

    // Tercer consulta para actualizar la calificación del tercer parcial
    $sql3 = "UPDATE calificaciones SET nota = $parcial3 WHERE id_alumno = '$idusuario' AND id_asignatura = '$idmateria' AND parcial = 3";

    // Ejecutamos las consultas
    $resultado1 = $conn->ejecutarSQL($sql1); //Ejecutamos la consulta
    $resultado2 = $conn->ejecutarSQL($sql2); //Ejecutamos la consulta
    $resultado3 = $conn->ejecutarSQL($sql3); //Ejecutamos la consulta
    return "Calificaciones actualizadas exitosamente."; //Retornamos mensaje de exito si se actualizan correctamente
}

?>
