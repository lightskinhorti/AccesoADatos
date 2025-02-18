<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XML Control Panel</title>
    <style>
        /* Estilos generales del cuerpo */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #0056b3;
        }

        /* Estilos para carpetas */
        .folder {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #fff;
            border-radius: 5px;
        }

        .folder h2 {
            margin-bottom: 10px;
            color: #007bff;
        }

        /* Lista de archivos */
        .file-list {
            list-style-type: none;
            padding: 0;
        }

        .file-list li {
            padding: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .file-list li:last-child {
            border-bottom: none;
        }

        /* Botones */
        button {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 5px;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Modal (ventana emergente) */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-height: 80%;
            overflow-y: auto;
        }

        /* Contenido del modal (archivo XML) */
        .modal-content pre {
            font-family: monospace;
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        /* Botón para cerrar el modal */
        .close-btn {
            display: block;
            margin-left: auto;
            margin-right: 0;
            background-color: #dc3545;
            padding: 5px 10px;
        }

        .close-btn:hover {
            background-color: #a71d2a;
        }
    </style>
</head>
<body>
    <h1>XML Control Panel</h1>

    <?php
    /**
     * Función recursiva que escanea un directorio en busca de archivos y carpetas.
     * Muestra la estructura del directorio en formato HTML.
     * 
     * @param string $baseDir Ruta del directorio a escanear
     */
    function parseDirectory($baseDir)
    {
        // Obtener lista de archivos y carpetas en el directorio
        $items = scandir($baseDir);
        echo "<ul class='file-list'>";

        foreach ($items as $item) {
            // Ignorar los directorios especiales "." y ".."
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $baseDir . '/' . $item; // Ruta completa del archivo/carpeta

            if (is_dir($fullPath)) {
                // Si es un directorio, mostrarlo y llamar recursivamente la función
                echo "<div class='folder'>";
                echo "<h2>Folder: $item</h2>";
                parseDirectory($fullPath);
                echo "</div>";
            } elseif (pathinfo($fullPath, PATHINFO_EXTENSION) === 'xml') {
                // Si es un archivo XML, agregarlo a la lista con botones de acciones
                echo "<li>
                        $item 
                        <button onclick=\"viewContent('$fullPath')\">View</button>
                        <button onclick=\"downloadFile('$fullPath')\">Download</button>
                      </li>";
            }
        }

        echo "</ul>";
    }

    $baseDir = 'xml'; // Carpeta donde se almacenan los archivos XML

    // Verificar si la carpeta existe antes de intentar leerla
    if (!is_dir($baseDir)) {
        echo "<p>XML base directory does not exist.</p>";
        exit;
    }

    // Llamar a la función para escanear el directorio base
    parseDirectory($baseDir);
    ?>

    <!-- Modal para ver archivos XML -->
    <div id="contentModal" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal()">Close</button>
            <pre id="contentViewer"></pre>
        </div>
    </div>

    <script>
        /**
         * Carga y muestra el contenido de un archivo XML en un modal.
         * @param {string} filePath - Ruta del archivo a cargar.
         */
        function viewContent(filePath) {
            fetch(filePath)
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch file content.');
                    return response.text();
                })
                .then(content => {
                    document.getElementById('contentViewer').textContent = content;
                    document.getElementById('contentModal').style.display = 'flex';
                })
                .catch(error => {
                    alert('Error loading file content: ' + error.message);
                });
        }

        /**
         * Cierra el modal de visualización de archivos.
         */
        function closeModal() {
            document.getElementById('contentModal').style.display = 'none';
        }

        /**
         * NUEVA FUNCIONXW
         * Descarga un archivo XML desde el servidor.
         * @param {string} filePath - Ruta del archivo a descargar.
         */
        function downloadFile(filePath) {
            const link = document.createElement('a');
            link.href = filePath;
            link.download = filePath.split('/').pop(); // Extraer el nombre del archivo
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
