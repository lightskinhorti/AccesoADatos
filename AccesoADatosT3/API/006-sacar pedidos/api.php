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

// Verificar si se ha proporcionado el parámetro 'o' en la URL
if (!isset($_GET['o'])) {
    die(json_encode(['resultado' => 'ko', 'mensaje' => 'Parámetro "o" no proporcionado']));
}

// Obtener la opción del parámetro 'o'
$opcion = $_GET['o'];

// Manejar la opción seleccionada
switch ($opcion) {
    case "clientes":
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

        // Ejecutar la consulta en la base de datos
        $resultado = mysqli_query($mysqli, $peticion);

        // Verificar si la consulta se ejecutó correctamente
        if (!$resultado) {
            die(json_encode(['resultado' => 'ko', 'mensaje' => 'Error en la consulta: ' . mysqli_error($mysqli)]));
        }

        // Inicializar un array para almacenar los datos obtenidos
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

        // Convertir el array de datos a formato JSON con formato legible (JSON_PRETTY_PRINT)
        echo json_encode($output, JSON_PRETTY_PRINT);
        break;

    default:
        // Opción no reconocida
        echo json_encode(['resultado' => 'ko', 'mensaje' => 'Opción no válida']);
        break;
}

// Cerrar la conexión a la base de datos
mysqli_close($mysqli);

?>