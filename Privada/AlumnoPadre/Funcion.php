<?php
include('../../Conexion/conexionBD.php');
if (session_status() === PHP_SESSION_NONE) session_start();

//Funcion para obtener el grupo del alumno que inicio sesion
function obtenergrupo(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    
    //Consulta para obtener el grupo
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT alumnos.id_grupo FROM alumnos WHERE alumnos.id_alumno = '$id_alumno'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    $idgrupo_fila = $resultado->fetch_assoc();
    $idgrupo = $idgrupo_fila['id_grupo'];
    return $idgrupo;
}

//Funcion para consultar los alumnos de un grupo
function consultaralumnos(){
    //Obtenemos el id del grupo llamando a función
    $idgrupo = obtenergrupo();

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT usuarios.id_usuario, usuarios.nombre, usuarios.apellidos FROM usuarios JOIN alumnos
    ON alumnos.id_alumno = usuarios.id_usuario WHERE alumnos.id_grupo = '$idgrupo' ORDER BY usuarios.apellidos";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar las asignaturas de un alumno y el profesor que la imparte
function consultarasignaturasprofesores(){
    //Obtenemos el id del grupo llamando a la función
    $idgrupo = obtenergrupo();

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT DISTINCT asignaturas.nombre_asignatura, usuarios.nombre, usuarios.apellidos FROM horarios JOIN usuarios
    ON horarios.id_profesor = usuarios.id_usuario JOIN asignaturas ON asignaturas.id_asignatura = horarios.id_asignatura
    WHERE horarios.id_grupo = '$idgrupo'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar las asignatruas de un grupo
function consultarasignaturas(){
    //Obtenemos el id del grupo llamando a la función
    $idgrupo = obtenergrupo();

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT DISTINCT asignaturas.id_asignatura, asignaturas.nombre_asignatura FROM horarios 
    JOIN asignaturas ON asignaturas.id_asignatura = horarios.id_asignatura
    WHERE horarios.id_grupo = '$idgrupo' ORDER BY asignaturas.nombre_asignatura";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}


//Funcion para consultar el horario de un grupo para un día en específico
function consultarhorario(){
    //Obtenemos el id del grupo llamando a la función
    $idgrupo = obtenergrupo();
    $id_asignatura = $_POST['materia']; //Obtenemos el id de la materia seleccionada

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT asignaturas.nombre_asignatura, horarios.dia, horarios.horaInicio, horarios.horaFin FROM horarios JOIN asignaturas
    ON horarios.id_asignatura = asignaturas.id_asignatura WHERE horarios.id_grupo = '$idgrupo' AND horarios.id_asignatura = '$id_asignatura'
    ORDER BY FIELD(dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'), horarios.horaInicio";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar las materias en las que un alumno tiene faltas
function consultarasignaturasfaltas(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT DISTINCT asignaturas.id_asignatura, asignaturas.nombre_asignatura FROM falta
    JOIN asignaturas ON asignaturas.id_asignatura = falta.id_asignatura
    WHERE falta.id_alumno = '$id_alumno' ORDER BY asignaturas.nombre_asignatura";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar las faltas de una materia
function consultarfaltas(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    $id_asignatura = $_POST['materia']; //Obtenemos el id de la materia seleccionada

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT falta.fecha_falta, asignaturas.nombre_asignatura, falta.justificada FROM falta
    JOIN asignaturas ON asignaturas.id_asignatura = falta.id_asignatura
    WHERE falta.id_alumno = '$id_alumno' AND falta.id_asignatura = '$id_asignatura' ORDER BY falta.fecha_falta";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar las califiaciones de una materia específica
function consultarcalificaciones(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    $id_asignatura = $_POST['materia']; //Obtenemos el id de la materia seleccionada
    $conn = new conexion(); //Hacemos la conexión a la base de datos

    if($id_asignatura == 0) { //Si se selecciona la opción de ver todas las materias
        // Consulta para obtener las calificaciones del alumno en todas las materias
        $sql = "SELECT asignaturas.id_asignatura, asignaturas.nombre_asignatura, calificaciones.parcial, calificaciones.nota 
        FROM calificaciones JOIN asignaturas ON calificaciones.id_asignatura = asignaturas.id_asignatura
        WHERE calificaciones.id_alumno = '$id_alumno' ORDER BY asignaturas.nombre_asignatura, calificaciones.parcial";
    }

    else {
        // Consulta para obtener las calificaciones del alumno en la materia seleccionada 
        $sql = "SELECT asignaturas.id_asignatura, asignaturas.nombre_asignatura, calificaciones.parcial, calificaciones.nota 
        FROM calificaciones JOIN asignaturas ON calificaciones.id_asignatura = asignaturas.id_asignatura
        WHERE calificaciones.id_alumno = '$id_alumno' AND calificaciones.id_asignatura = '$id_asignatura' 
        ORDER BY asignaturas.nombre_asignatura, calificaciones.parcial";
    }

    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta

    // Crear un array para agrupar las calificaciones por alumno usando el ID como clave
    $calificacionesAgrupadas = [];

    //Recorremos todos los registros de calificaciones
    while ($registro = $resultado->fetch_assoc()) {
        $id_materia = $registro['id_asignatura']; //Obtenemos el ID de la materia

        // Si aún no se ha agregado la materia al array, lo hacemos
        if (!isset($calificacionesAgrupadas[$id_materia])) {
            //Agregamos la materia con su nombre y califiqaciones vacías
            $calificacionesAgrupadas[$id_materia] = [
                'nombre_asignatura' => $registro['nombre_asignatura'],
                'parcial_1' => '',
                'parcial_2' => '',
                'parcial_3' => '',
                'promedio' => 0,
            ];
        }

        $parcial = $registro['parcial']; //Obtenemos el número de parcial
        $nota = $registro['nota']; //Obtenemos la nota
                        
        // Guardamos la nota en el arreglo según el número de parcial
        if ($parcial == 1) {
            $calificacionesAgrupadas[$id_materia]['parcial_1'] = $nota;
        } elseif ($parcial == 2) {
            $calificacionesAgrupadas[$id_materia]['parcial_2'] = $nota;
        } elseif ($parcial == 3) {
            $calificacionesAgrupadas[$id_materia]['parcial_3'] = $nota;
            
            //Si las tres calificaciones ya están asignadas, calculamos el promedio
            //Verificamos que las calificaciones no sean cero
            if($calificacionesAgrupadas[$id_materia]['parcial_1'] != '0' 
                && $calificacionesAgrupadas[$id_materia]['parcial_2'] != '0' 
                && $calificacionesAgrupadas[$id_materia]['parcial_3'] != '0') {
                // Calculamos el promedio de las calificaciones
                $promedio = (
                    $calificacionesAgrupadas[$id_materia]['parcial_1'] + 
                    $calificacionesAgrupadas[$id_materia]['parcial_2'] + 
                    $calificacionesAgrupadas[$id_materia]['parcial_3']) / 3;
                $calificacionesAgrupadas[$id_materia]['promedio'] = round($promedio, 2); //Redondeamos a 2 decimales
            }
        }
    }

    return $calificacionesAgrupadas; //Retornamos las calificaciones agrupadas por materia
}

//Funcion para consultar el nombre completo del profesor encargado de clubes sabatinos
function consultarprofesorsabatino(){
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT usuarios.nombre, usuarios.apellidos FROM usuarios WHERE usuarios.tipo_usuario = 'profesor_sabatino'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    $profesor_sabatino = $resultado->fetch_assoc();
    $nombre_completo = $profesor_sabatino['nombre'] . " " . $profesor_sabatino['apellidos'];
    return $nombre_completo; //Retornamos el nombre completo del profesor sabatino
}

//Funcion para consultar si el alumno está inscrito en sabatinos
function estasabatinos(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT alumnos.esta_sabatinos FROM alumnos WHERE alumnos.id_alumno = '$id_alumno'";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    $estado = $resultado->fetch_assoc();
    $inscrito_sabatinos = $estado['esta_sabatinos']; //Obtenemos el estado
    return $inscrito_sabatinos; //Retornamos el estado
}

//Funcion para consultar las asistencias de un alumno a sabatinos
function consultarasistenciassabatinos(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT sabatinos.fecha_asistencia FROM sabatinos WHERE sabatinos.id_alumno = '$id_alumno' ORDER BY sabatinos.fecha_asistencia";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar las fechas en las que el alumno tiene faltas
function consultarfechasfaltas(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    //Consulta sql para obtener las fechas disponibles para justificar
    //Esta consulta obtiene las fechas de faltas que no han sido justificadas y que no están en la tabla de justificantes
    $sql = "SELECT DISTINCT falta.fecha_falta FROM falta WHERE id_alumno = '$id_alumno'
        AND falta.justificada = 0 AND falta.fecha_falta NOT IN (
            SELECT justificantes.fecha_justificar FROM justificantes 
            WHERE justificantes.id_alumno = '$id_alumno'
            AND justificantes.estado IN ('pendiente', 'aprobado')) ORDER BY falta.fecha_falta";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para solicitar un justificante
function solicitarjustificante($fecha_justificar){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    $motivo = $_POST['motivo']; //Obtenemos el motivo de la falta

    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "INSERT INTO justificantes (id_alumno, fecha_justificar, estado, motivo) 
        VALUES ('$id_alumno', '$fecha_justificar', 'pendiente', '$motivo')";
    $resultado = $conn->ejecutarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

//Funcion para consultar los justificantes solicitados por un alumno
function consultarjustificantes(){
    $id_alumno = $_SESSION['id_usuario']; //Obtenemos el id del alumno que inicio sesión
    $conn = new conexion(); //Hacemos la conexión a la base de datos
    $sql = "SELECT justificantes.fecha_justificar, justificantes.estado, justificantes.motivo FROM justificantes 
        WHERE justificantes.id_alumno = '$id_alumno' ORDER BY justificantes.fecha_justificar";
    $resultado = $conn->consultarSQL($sql); //Ejecutamos la consulta
    return $resultado; //Retornamos el resultado de la consulta
}

