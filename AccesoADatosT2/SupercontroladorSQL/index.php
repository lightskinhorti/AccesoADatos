<?php
    // Configuración de errores para mostrar información detallada durante el desarrollo
    ini_set('display_errors', 1);                          // Habilita la visualización de errores de PHP
    ini_set('display_startup_errors', 1);                  // Habilita la visualización de errores de inicio
    error_reporting(E_ALL);                                // Reporta todos los tipos de errores

    // Incluir el archivo que contiene la definición de la clase `ConexionDB`
    include "ConexionDB.php";                              // Asegúrate de que el archivo está en el mismo directorio o ajusta la ruta

    // Crear una nueva instancia de la clase `ConexionDB`
    $conexion = new ConexionDB();                          // Inicializa la conexión a la base de datos usando el constructor

    // Ejecutar el método `seleccionaTabla` de la clase `ConexionDB` para obtener datos de la tabla `lineaspedido`
    // Este método retorna un JSON con los datos de la tabla
    echo $conexion->seleccionaTabla("lineaspedido");       // Muestra en pantalla el resultado del método en formato JSON
?>
