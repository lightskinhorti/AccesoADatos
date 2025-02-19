<?php

/**
 * Clase SimpleFileDB
 * 
 * Esta clase proporciona una interfaz sencilla para interactuar con el motor de base de datos 
 * implementado en C++ (mydbapp). Permite realizar operaciones de selección, inserción y, 
 * NUEVA FUNCIÓN, actualización de registros mediante comandos de shell.
 */
class SimpleFileDB
{
    // Ruta al ejecutable del motor de base de datos en C++
    private $executable;
    // Nombre de la carpeta que actúa como base de datos
    private $databaseName;

    /**
     * Constructor
     *
     * @param string $databaseName Nombre de la carpeta de la base de datos (ej. MyDatabase)
     */
    public function __construct($databaseName)
    {
        // Establezco la ruta al ejecutable; ajústalo según tu configuración
        $this->executable = "mydbapp.exe";
        $this->databaseName = $databaseName;
    }

    /**
     * Operación SELECT:
     * Llama al programa C++ con el comando "select" para listar todos los archivos .json 
     * en la carpeta de la base de datos. Puede devolver la salida cruda o parseada a un array.
     *
     * @param bool $parseJson Si es true, se parsea la salida en un array; de lo contrario, se devuelve como string.
     * @return mixed Salida en formato string o array.
     * @throws RuntimeException si falla la ejecución del comando.
     */
    public function select($parseJson = false)
    {
        // Construyo el comando de shell de forma segura
        $cmd = escapeshellcmd($this->executable) . ' ' 
             . escapeshellarg($this->databaseName) . ' select';

        // Ejecuto el comando
        $output = shell_exec($cmd);
        if ($output === null) {
            throw new RuntimeException("Failed to execute select command.");
        }

        // Si no se requiere parseo, devuelvo la salida cruda
        if (!$parseJson) {
            return $output;
        }

        // Parseo la salida en un array. Se espera un formato de salida similar a:
        // File: record_123456789.json
        // Content:
        // { ... json data ... }
        $lines = explode("\n", $output);
        $results = [];
        $currentFile = null;
        $currentJson = "";

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, 'File: ') === 0) {
                // Al encontrar un nuevo registro, si existe uno anterior, lo almaceno
                if ($currentFile !== null && strlen($currentJson) > 0) {
                    $decoded = json_decode($currentJson, true);
                    $results[] = [
                        'file' => $currentFile,
                        'data' => $decoded !== null ? $decoded : $currentJson
                    ];
                }
                // Reinicio para el nuevo registro
                $currentFile = substr($line, strlen('File: '));
                $currentJson = "";
            } elseif ($line === 'Content:' || $line === '') {
                // Se omiten las líneas literales 'Content:' y líneas vacías
                continue;
            } else {
                // Se acumulan las líneas de JSON
                $currentJson .= ($currentJson === "" ? $line : "\n" . $line);
            }
        }

        // Al finalizar, almaceno el último registro si existe
        if ($currentFile !== null && strlen($currentJson) > 0) {
            $decoded = json_decode($currentJson, true);
            $results[] = [
                'file' => $currentFile,
                'data' => $decoded !== null ? $decoded : $currentJson
            ];
        }

        return $results;
    }

    /**
     * Operación INSERT:
     * Llama al programa C++ con el comando "insert" para insertar datos JSON en la base de datos.
     *
     * @param string|array $jsonData Los datos a insertar (cadena JSON o array de PHP).
     * @return string Salida del programa C++.
     * @throws RuntimeException si falla la ejecución del comando.
     */
    public function insert($jsonData)
{
    // Si es un array, convertirlo a JSON
    if (is_array($jsonData)) {
        $jsonData = json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($jsonData === false) {
            throw new RuntimeException("Error al codificar JSON: " . json_last_error_msg());
        }
    }

    // Escapar comillas dobles para shell_exec()
    $jsonData = str_replace('"', '\"', $jsonData);

    // Construir el comando correctamente
    $cmd = sprintf(
        '%s %s insert "%s"',
        escapeshellcmd($this->executable),
        escapeshellarg($this->databaseName),
        $jsonData
    );

    // Ejecutar el comando
    echo "Comando ejecutado: $cmd\n"; // Debugging
    $output = shell_exec($cmd);

    if ($output === null) {
        throw new RuntimeException("Error al ejecutar el comando insert.");
    }

    return $output;
}




    /**
     * NUEVA FUNCIÓN: Operación UPDATE
     * Llama al programa C++ con el comando "update" para actualizar un registro existente.
     *
     * @param string $fileName El nombre del archivo que se desea actualizar (ej. record_123456789.json).
     * @param string|array $jsonData Los nuevos datos en formato JSON o array de PHP que reemplazarán el contenido actual.
     * @return string Salida del programa C++.
     * @throws RuntimeException si falla la ejecución del comando.
     */
    public function update($fileName, $jsonData)
    {
        // Si se pasa un array, lo convierto a JSON
        if (is_array($jsonData)) {
            $jsonData = json_encode($jsonData, JSON_UNESCAPED_SLASHES);
        }

        // Construyo el comando de shell para la operación update:
        // Formato: <executable> <databaseName> update <fileName> <jsonData>
        $cmd = escapeshellcmd($this->executable) . ' '
             . escapeshellarg($this->databaseName) . ' update '
             . escapeshellarg($fileName) . ' '
             . escapeshellarg($jsonData);

        $output = shell_exec($cmd);
        if ($output === null) {
            throw new RuntimeException("Failed to execute update command.");
        }

        return $output;
    }
}

?>
