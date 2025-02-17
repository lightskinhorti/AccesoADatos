<?php

// Activar la visualización de errores
ini_set('display_errors', 1); // Activo errores
ini_set('display_startup_errors', 1); // Activo errores de inicio
error_reporting(E_ALL);

// Conexión a la base de datos
$mysqli = mysqli_connect("localhost", "crimson", "crimson", "crimson");

// Verificar si la conexión fue exitosa
if (!$mysqli) {
    die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error de conexión: ' . mysqli_connect_error()]));
}

/**
 * Valida la clave de acceso en la base de datos.
 *
 * @param mysqli $mysqli Conexión a la base de datos.
 * @param string $clave Clave de acceso a validar.
 * @return bool True si la clave es válida, False en caso contrario.
 */
function validarClave($mysqli, $clave) {
    // Preparar la consulta SQL para evitar inyecciones SQL
    $peticion = "SELECT * FROM clavesapi WHERE clave = ?";
    $stmt = mysqli_prepare($mysqli, $peticion);

    // Verificar si la preparación de la consulta fue exitosa
    if (!$stmt) {
        die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error al preparar la consulta: ' . mysqli_error($mysqli)]));
    }

    // Vincular el parámetro a la consulta
    mysqli_stmt_bind_param($stmt, "s", $clave);

    // Ejecutar la consulta
    if (!mysqli_stmt_execute($stmt)) {
        die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error al validar la clave: ' . mysqli_stmt_error($stmt)]));
    }

    // Obtener el resultado
    $resultado = mysqli_stmt_get_result($stmt);

    // Verificar si la clave es válida
    if (mysqli_fetch_assoc($resultado)) {
        return true; // Clave válida
    } else {
        return false; // Clave inválida
    }
}

/**
 * Inserta un nuevo cliente en la base de datos.
 *
 * @param mysqli $mysqli Conexión a la base de datos.
 * @param string $nombre Nombre del cliente.
 * @param string $apellidos Apellidos del cliente.
 * @return void
 */
function insertarCliente($mysqli, $nombre, $apellidos) {
    // Preparar la consulta SQL para evitar inyecciones SQL
    $peticion = "INSERT INTO clientes (nombre, apellidos) VALUES (?, ?)";
    $stmt = mysqli_prepare($mysqli, $peticion);

    // Verificar si la preparación de la consulta fue exitosa
    if (!$stmt) {
        die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error al preparar la consulta: ' . mysqli_error($mysqli)]));
    }

    // Vincular los parámetros a la consulta
    mysqli_stmt_bind_param($stmt, "ss", $nombre, $apellidos);

    // Ejecutar la consulta
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['resultado' => 'ok', 'mensaje' => 'Cliente insertado correctamente']);
    } else {
        echo json_encode(['resultado' => 'ko', 'mensaje' => 'Error al insertar el cliente: ' . mysqli_stmt_error($stmt)]);
    }

    // Cerrar la consulta preparada
    mysqli_stmt_close($stmt);
}

// Verificar si se ha proporcionado el parámetro 'o' en la URL
if (isset($_GET['o'])) {
    // Manejar la opción seleccionada
    switch ($_GET['o']) {
        case "clientes":
            // Incluir el archivo que obtiene los datos de los clientes
            include "inc/damepedidos.php";
            break;

        case "insertarCliente":
            // Verificar si se ha proporcionado la clave de acceso
            if (isset($_POST['clave'])) {
                // Validar la clave de acceso
                if (validarClave($mysqli, $_POST['clave'])) {
                    // Verificar si se han proporcionado los parámetros necesarios
                    if (isset($_POST['nombre']) && isset($_POST['apellidos'])) {
                        // Llamar a la función para insertar un cliente
                        insertarCliente($mysqli, $_POST['nombre'], $_POST['apellidos']);
                    } else {
                        echo json_encode(['resultado' => 'ko', 'mensaje' => 'Faltan parámetros: nombre y apellidos']);
                    }
                } else {
                    echo json_encode(['resultado' => 'ko', 'mensaje' => 'Acceso no permitido: Clave inválida']);
                }
            } else {
                echo json_encode(['resultado' => 'ko', 'mensaje' => 'Falta la clave de acceso']);
            }
            break;

        default:
            echo json_encode(['resultado' => 'ko', 'mensaje' => 'Opción no válida']);
    }
} else {
    echo json_encode(['resultado' => 'ko', 'mensaje' => 'Parámetro "o" no proporcionado']);
}

// Cerrar la conexión a la base de datos
mysqli_close($mysqli);

?>