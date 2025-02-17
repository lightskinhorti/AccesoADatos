<?php

// Conexión a la base de datos
$mysqli = mysqli_connect("localhost", "crimson", "crimson", "crimson");

// Verificar si la conexión fue exitosa
if (!$mysqli) {
    die("Error de conexión: " . mysqli_connect_error()); // Si hay un error, se detiene la ejecución y se muestra el mensaje
}

// Consulta SQL para seleccionar nombres y apellidos de la tabla "clientes"
// Se utilizan alias para mejorar la legibilidad de los nombres de las columnas en el resultado
$peticion = "
    SELECT 
        clientes.nombre AS 'Nombre del cliente',
        clientes.apellidos AS 'Apellidos del cliente'
    FROM 
        clientes";

// Ejecutar la consulta en la base de datos
$resultado = mysqli_query($mysqli, $peticion);

// Verificar si la consulta se ejecutó correctamente
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($mysqli)); // Si hay un error, se detiene la ejecución y se muestra el mensaje
}

// Inicializar un array para almacenar los datos obtenidos
$datos = [];

// Recorrer cada fila del resultado y almacenarla en el array $datos
while ($fila = mysqli_fetch_assoc($resultado)) {
    $datos[] = $fila; // Agregar cada fila como un elemento del array
}

// Convertir el array de datos a formato JSON
$json = json_encode($datos);

// Verificar si la conversión a JSON fue exitosa
if ($json === false) {
    die("Error al convertir a JSON: " . json_last_error_msg()); // Si hay un error, se detiene la ejecución y se muestra el mensaje
}

// Enviar la respuesta JSON al cliente
echo $json;

// Cerrar la conexión a la base de datos
mysqli_close($mysqli);

?>