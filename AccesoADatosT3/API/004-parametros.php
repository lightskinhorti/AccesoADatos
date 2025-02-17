<?php

// Conexión a la base de datos
$mysqli = mysqli_connect("localhost", "crimson", "crimson", "crimson");

// Verificar si la conexión fue exitosa
if (!$mysqli) {
    die("Error de conexión: " . mysqli_connect_error()); // Si hay un error, se detiene la ejecución y se muestra el mensaje
}

// Verificar si se ha proporcionado el parámetro 'o' en la URL
if (!isset($_GET['o'])) {
    die(json_encode(['resultado' => 'ko', 'mensaje' => 'Parámetro "o" no proporcionado']));
}

// Obtener la opción del parámetro 'o'
$opcion = $_GET['o'];

// Manejar la opción seleccionada
switch ($opcion) {
    case "clientes":
        // Consulta para obtener todos los clientes
        $peticion = "
            SELECT 
                clientes.nombre AS 'Nombre del cliente',
                clientes.apellidos AS 'Apellidos del cliente'
            FROM 
                clientes";
        break;

    case "cliente":
        // Verificar si se ha proporcionado el parámetro 'id'
        if (!isset($_GET['id'])) {
            die(json_encode(['resultado' => 'ko', 'mensaje' => 'Parámetro "id" no proporcionado']));
        }

        // Obtener el ID del cliente
        $id = intval($_GET['id']); // Convertir a entero para mayor seguridad

        // Consulta para obtener un cliente específico
        $peticion = "
            SELECT 
                clientes.nombre AS 'Nombre del cliente',
                clientes.apellidos AS 'Apellidos del cliente'
            FROM 
                clientes
            WHERE 
                clientes.Identificador = $id";
        break;

    default:
        // Opción no reconocida
        echo json_encode(['resultado' => 'ko', 'mensaje' => 'Opción no válida']);
        exit;
}

// Ejecutar la consulta en la base de datos
$resultado = mysqli_query($mysqli, $peticion);

// Verificar si la consulta se ejecutó correctamente
if (!$resultado) {
    die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error en la consulta: ' . mysqli_error($mysqli)]));
}

// Inicializar un array para almacenar los datos obtenidos
$datos = [];

// Recorrer cada fila del resultado y almacenarla en el array $datos
while ($fila = mysqli_fetch_assoc($resultado)) {
    $datos[] = $fila; // Agregar cada fila como un elemento del array
}

// Convertir el array de datos a formato JSON con formato legible (JSON_PRETTY_PRINT)
$json = json_encode($datos, JSON_PRETTY_PRINT);

// Verificar si la conversión a JSON fue exitosa
if ($json === false) {
    die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error al convertir a JSON: ' . json_last_error_msg()]));
}

// Enviar la respuesta JSON al cliente
echo $json;

// Cerrar la conexión a la base de datos
mysqli_close($mysqli);

?>