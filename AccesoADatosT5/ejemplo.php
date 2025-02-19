<?php
require 'conectorphp.php';

// Instanciamos la clase con el nombre de la base de datos 'clientes'
$db = new SimpleFileDB('clientes');

// Insertamos un nuevo registro
$dataToInsert = ['nombre' => 'Javier', 'edad' => 19];
$insertResult = $db->insert($dataToInsert);
echo "Resultado de inserción: " . $insertResult . "\n";

// Obtenemos todos los registros y los mostramos de manera legible
$rows = $db->select(true);

echo "Registros actuales en la base de datos:\n";
if (!empty($rows)) {
    print_r($rows);
} else {
    echo "No hay registros disponibles.\n";
}

// NUEVA FUNCIÓN: Actualizar un registro (simulando que sabemos el nombre del archivo)
$archivoActualizar = "record_1737486040.json"; // Este nombre debe existir en la base de datos
$dataToUpdate = ['nombre' => 'Javier', 'edad' => 19];

$updateResult = $db->update($archivoActualizar, $dataToUpdate);
echo "Resultado de actualización: " . $updateResult . "\n";

?>
