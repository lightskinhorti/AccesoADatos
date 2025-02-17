<?php

/**
 * Obtiene los datos de los clientes, sus pedidos y las líneas de pedido desde la base de datos.
 *
 * @param mysqli $mysqli Conexión a la base de datos.
 * @return array Datos estructurados de clientes, pedidos y líneas de pedido.
 */
function obtenerDatosClientes($mysqli) {
    // Consulta SQL para obtener clientes, pedidos y líneas de pedido
    $peticion = "
        SELECT 
            clientes.nombre AS nombre,
            clientes.apellidos AS apellidos,
            pedidos.fecha AS fecha_pedido,
            lineaspedido.productos_nombre AS producto,
            lineaspedido.cantidad AS cantidad
        FROM clientes
        LEFT JOIN pedidos ON clientes.Identificador = pedidos.clientes_nombre
        LEFT JOIN lineaspedido ON pedidos.Identificador = lineaspedido.pedidos_fecha
    ";

    // Ejecutar la consulta
    $resultado = mysqli_query($mysqli, $peticion);

    // Verificar si la consulta se ejecutó correctamente
    if (!$resultado) {
        die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error en la consulta: ' . mysqli_error($mysqli)]));
    }

    return $resultado;
}

/**
 * Estructura los datos de clientes, pedidos y líneas de pedido en un formato JSON.
 *
 * @param mysqli_result $resultado Resultado de la consulta SQL.
 * @return array Datos estructurados en un formato específico.
 */
function estructurarDatos($resultado) {
    $datos = [];

    // Recorrer cada fila del resultado
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // Construir la estructura JSON
        $cliente_key = $fila['nombre'] . " " . $fila['apellidos'];

        // Si el cliente no existe en el array, agregarlo
        if (!isset($datos[$cliente_key])) {
            $datos[$cliente_key] = [
                "cliente" => [
                    "nombre" => $fila['nombre'],
                    "apellidos" => $fila['apellidos']
                ],
                "pedidos" => []
            ];
        }

        // Si hay un pedido, agregarlo
        if ($fila['fecha_pedido']) {
            $pedido_key = $fila['fecha_pedido'];

            // Si el pedido no existe en el array, agregarlo
            if (!isset($datos[$cliente_key]["pedidos"][$pedido_key])) {
                $datos[$cliente_key]["pedidos"][$pedido_key] = [
                    "fecha" => $fila['fecha_pedido'],
                    "lineaspedido" => []
                ];
            }

            // Si hay un producto y una cantidad, agregarlos a las líneas de pedido
            if ($fila['producto'] && $fila['cantidad']) {
                $datos[$cliente_key]["pedidos"][$pedido_key]["lineaspedido"][] = [
                    "producto" => $fila['producto'],
                    "cantidad" => $fila['cantidad']
                ];
            }
        }
    }

    // Reorganizar el array para eliminar índices de cliente y pedidos
    $output = [];
    foreach ($datos as $cliente) {
        $cliente_pedidos = [];
        foreach ($cliente['pedidos'] as $pedido) {
            $cliente_pedidos[] = $pedido;
        }
        $output[] = [
            "cliente" => $cliente['cliente'],
            "pedidos" => $cliente_pedidos
        ];
    }

    return $output;
}

// Conexión a la base de datos
$mysqli = mysqli_connect("localhost", "crimson", "crimson", "crimson");

// Verificar si la conexión fue exitosa
if (!$mysqli) {
    die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error de conexión: ' . mysqli_connect_error()]));
}

// Obtener los datos de los clientes
$resultado = obtenerDatosClientes($mysqli);

// Estructurar los datos en un formato JSON
$datosEstructurados = estructurarDatos($resultado);

// Convertir el array de datos a formato JSON con formato legible (JSON_PRETTY_PRINT)
echo json_encode($datosEstructurados, JSON_PRETTY_PRINT);

// Cerrar la conexión a la base de datos
mysqli_close($mysqli);

?>