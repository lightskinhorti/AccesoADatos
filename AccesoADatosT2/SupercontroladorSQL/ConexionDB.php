<?php

class ConexionDB {
    // Propiedades privadas para la configuración de la base de datos
    private $servidor;
    private $usuario;
    private $contrasena;
    private $basededatos;
    private $conexion;

    /**
     * Constructor de la clase.
     * Establece los parámetros de conexión y conecta a la base de datos.
     */
    public function __construct() {
        $this->servidor = "localhost";  // Dirección del servidor MySQL
        $this->usuario = "crimson";     // Usuario de la base de datos
        $this->contrasena = "crimson";  // Contraseña del usuario
        $this->basededatos = "crimson"; // Nombre de la base de datos

        // Crear la conexión a la base de datos
        $this->conexion = new mysqli(
            $this->servidor,
            $this->usuario,
            $this->contrasena,
            $this->basededatos
        );

        // Verificar si la conexión fue exitosa
        if ($this->conexion->connect_error) {
            die("Error en la conexión a la base de datos: " . $this->conexion->connect_error);
        }
    }

    /**
     * Método para obtener las restricciones de una tabla y sus datos.
     * @param string $tabla Nombre de la tabla a consultar.
     * @return string JSON con los datos de la tabla y las restricciones.
     */
    public function seleccionaTabla($tabla) {
        // Consultar restricciones de claves foráneas
        $queryRestricciones = "
            SELECT COLUMN_NAME, REFERENCED_TABLE_NAME
            FROM information_schema.key_column_usage
            WHERE table_name = ?
            AND REFERENCED_TABLE_NAME IS NOT NULL;
        ";
        $stmtRestricciones = $this->conexion->prepare($queryRestricciones);
        $stmtRestricciones->bind_param("s", $tabla);
        $stmtRestricciones->execute();
        $resultRestricciones = $stmtRestricciones->get_result();

        $restricciones = [];
        while ($row = $resultRestricciones->fetch_assoc()) {
            $restricciones[] = $row;
        }

        // Consultar todos los datos de la tabla
        $queryDatos = "SELECT * FROM $tabla;";
        $resultDatos = $this->conexion->query($queryDatos);

        $resultado = [];
        while ($row = $resultDatos->fetch_assoc()) {
            $fila = [];
            foreach ($row as $clave => $valor) {
                $pasas = true; // Asumimos que no hay restricciones
                foreach ($restricciones as $restriccion) {
                    if ($clave == $restriccion["COLUMN_NAME"]) {
                        // Consultar la tabla referenciada
                        $queryReferenciada = "SELECT * FROM " . $restriccion["REFERENCED_TABLE_NAME"];
                        $resultReferenciada = $this->conexion->query($queryReferenciada);

                        $cadena = "";
                        while ($rowReferenciada = $resultReferenciada->fetch_assoc()) {
                            $cadena .= implode("-", $rowReferenciada) . "-";
                        }
                        $fila[$clave] = $cadena;
                        $pasas = false;
                    }
                }
                if ($pasas) {
                    $fila[$clave] = $valor;
                }
            }
            $resultado[] = $fila;
        }
        return json_encode($resultado, JSON_PRETTY_PRINT);
    }

    /**
     * Método para listar las tablas de la base de datos.
     * @return string JSON con la lista de tablas.
     */
    public function listadoTablas() {
        $query = "SHOW TABLES;";
        $result = $this->conexion->query($query);

        $resultado = [];
        while ($row = $result->fetch_assoc()) {
            $resultado[] = $row;
        }
        return json_encode($resultado, JSON_PRETTY_PRINT);
    }

    /**
     * Método para insertar datos en una tabla.
     * @param string $tabla Nombre de la tabla.
     * @param array $valores Datos a insertar como un arreglo asociativo.
     * @return void
     */
    public function insertaTabla($tabla, $valores) {
        $campos = implode(",", array_keys($valores));
        $placeholders = implode(",", array_fill(0, count($valores), "?"));

        $query = "INSERT INTO $tabla ($campos) VALUES ($placeholders)";
        $stmt = $this->conexion->prepare($query);

        $tipos = str_repeat("s", count($valores));
        $stmt->bind_param($tipos, ...array_values($valores));
        $stmt->execute();
    }

    /**
     * Método para actualizar un registro en una tabla.
     * @param string $tabla Nombre de la tabla.
     * @param array $valores Datos a actualizar como un arreglo asociativo.
     * @param int $id Identificador del registro a actualizar.
     * @return void
     */
    public function actualizaTabla($tabla, $valores, $id) {
        $setClause = implode(", ", array_map(function ($campo) {
            return "$campo = ?";
        }, array_keys($valores)));

        $query = "UPDATE $tabla SET $setClause WHERE Identificador = ?";
        $stmt = $this->conexion->prepare($query);

        $tipos = str_repeat("s", count($valores)) . "i";
        $stmt->bind_param($tipos, ...array_values($valores), $id);
        $stmt->execute();
    }

    /**
     * Método para eliminar un registro de una tabla.
     * @param string $tabla Nombre de la tabla.
     * @param int $id Identificador del registro a eliminar.
     * @return void
     */
    public function eliminaTabla($tabla, $id) {
        $query = "DELETE FROM $tabla WHERE Identificador = ?";
        $stmt = $this->conexion->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    /**
     * Método privado para codificar una cadena.
     * @param string $entrada Cadena a codificar.
     * @return string Cadena codificada.
     */
    private function codifica($entrada) {
        return base64_encode($entrada);
    }

    /**
     * Método privado para decodificar una cadena.
     * @param string $entrada Cadena a decodificar.
     * @return string Cadena decodificada.
     */
    private function decodifica($entrada) {
        return base64_decode($entrada);
    }
}
?>
