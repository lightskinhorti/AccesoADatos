<?php
// Comprobamos si se ha enviado el formulario
if (isset($_POST['usuario'])) {
    // Habilitamos el reporte de errores para facilitar la depuración
    ini_set('display_errors', 1); // Muestra los errores de PHP
    ini_set('display_startup_errors', 1); // Muestra los errores al iniciar
    error_reporting(E_ALL); // Reporta todos los errores

    // Intentamos conectarnos a la base de datos con los parámetros recibidos
    $enlace = mysqli_connect(
        $_POST['servidor'], 
        $_POST['usuario'], 
        $_POST['contrasena'], 
        $_POST['basededatos']
    ) OR die("Error al conectar con la base de datos"); // Si no hay conexión, mostramos un mensaje de error

    // Leemos el archivo JSON que contiene el esquema de la base de datos
    $json = file_get_contents("004-modelodedatos.json");
    $datos = json_decode($json, true); // Decodificamos el JSON en un array asociativo

    // Iteramos sobre las tablas del esquema
    foreach ($datos as $dato) {
        $nombredetabla = $dato['nombre']; // Obtenemos el nombre de la tabla
        // Iniciamos la cadena SQL para crear la tabla
        $cadena = "CREATE TABLE ".$nombredetabla." ( 
            Identificador INT NOT NULL AUTO_INCREMENT , "; // Agregamos la columna Identificador como clave primaria

        // Iteramos sobre las columnas definidas para la tabla
        foreach ($dato['columnas'] as $columna) {
            // Añadimos el nombre y tipo de la columna a la cadena
            $cadena .= $columna['nombre']." ".$columna['tipo']." ";

            // Si la columna no es de tipo TEXT, agregamos su longitud
            if ($columna['tipo'] != "TEXT") {
                $cadena .= " (".$columna['longitud'].") ";
            }

            // Añadimos una coma al final de cada columna
            $cadena .= ","; 
        }

        // Añadimos la clave primaria a la tabla
        $cadena .= "PRIMARY KEY (Identificador) "; 
        $cadena .= " ) ENGINE = InnoDB"; // Especificamos el motor de la base de datos

        // Ejecutamos la consulta SQL para crear la tabla
        if (!mysqli_query($enlace, $cadena)) {
            echo "Error al crear la tabla: ".$nombredetabla." - ".mysqli_error($enlace);
        }
    }

    // Cerramos la conexión a la base de datos
    mysqli_close($enlace);
} else {
    // Si no se ha enviado el formulario, mostramos el formulario de instalación
?>
    <!doctype html>
    <html>
    <head>
        <title>Instalador de bases de datos</title>
        <style>
            body, html {
                height: 100%; padding: 0px; margin: 0px;
                background: url(fondo.jpg); background-size: cover;
            }
            form {
                width: 300px; height: 400px; background: rgba(255, 255, 255, 0.5);
                box-sizing: border-box; padding: 20px; border-radius: 20px;
                position: absolute; top: 50%; left: 50%; margin-left: -150px; margin-top: -200px;
                text-align: center; color: white;
                backdrop-filter: blur(20px);
            }
            form input {
                width: 100%; padding: 10px 0px; margin: 5px 0px;
                outline: none; border: none; border-bottom: 1px solid white; background: none;
            }
            form input::placeholder {
                color: white;
            }
            form input[type=submit] {
                background: white;
                border-radius: 20px;
                color: black;
            }
        </style>
    </head>
    <body>
        <form method="POST" action="?">
            <h1>Instalador</h1>
            <input type="text" name="usuario" placeholder="Usuario de la base de datos" required>
            <input type="password" name="contrasena" placeholder="Contraseña de la base de datos" required>
            <input type="text" name="servidor" placeholder="Servidor de la base de datos" required>
            <input type="text" name="basededatos" placeholder="Nombre de la base de datos" required>
            <input type="submit" value="Instalar">
        </form>
    </body>
    </html>
<?php
}
?>
