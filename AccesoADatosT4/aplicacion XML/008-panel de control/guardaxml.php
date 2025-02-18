<?php
// Configuración para mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Depuración: Ver contenido recibido en $_POST
var_dump($_POST);

// Verifica si la solicitud es de tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtiene los datos en formato JSON
    $rawData = file_get_contents('php://input');
    $parsedData = json_decode($rawData, true);

    // Valida si los datos son JSON válido
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsedData)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON data']);
        exit;
    }

    // Crea el objeto XML raíz
    $xml = new SimpleXMLElement('<root/>');

    // Función recursiva para convertir un array en XML
    function arrayToXml(array $data, SimpleXMLElement &$xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subNode = $xml->addChild(is_numeric($key) ? "item$key" : $key);
                arrayToXml($value, $subNode);
            } else {
                $xml->addChild(is_numeric($key) ? "item$key" : $key, htmlspecialchars($value));
            }
        }
    }

    arrayToXml($parsedData, $xml);

    // Convierte SimpleXMLElement a una cadena XML con formato
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $prettyXml = $dom->saveXML();

    // NUEVA FUNCIÓN: Valida si el XML generado es bien formado
    function validarXML($xmlString) {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($xmlString);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        
        return empty($errors); // Retorna true si no hay errores
    }

    // Verifica si el XML es válido antes de guardarlo
    if (!validarXML($prettyXml)) {
        http_response_code(500);
        echo json_encode(['error' => 'Generated XML is not well-formed']);
        exit;
    }

    // Genera un nombre de archivo basado en la marca de tiempo
    $filename = 'xml/'.$_GET['f'].'/' . date('U') . '.xml';

    // Guarda el XML en un archivo
    if (file_put_contents($filename, $prettyXml)) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Data saved to XML', 'file' => $filename]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save XML']);
    }
} else {
    // Manejo de método de solicitud no permitido
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method. Use POST.']);
}
