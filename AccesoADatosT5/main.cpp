#include <iostream>
#include <fstream>
#include <string>
#include <filesystem>
#include <chrono>
#include <ctime>

// Función principal del programa
int main(int argc, char* argv[])
{
    // ----------------------------------------------------------------------
    // Uso esperado:
    //   1) <program> <databaseName> select
    //   2) <program> <databaseName> insert <jsonData>
    //   3) NUEVA FUNCIONALIDAD - <program> <databaseName> update <fileName> <jsonData>
    //
    // Ejemplos:
    //   mydbapp MyDatabase select
    //   mydbapp MyDatabase insert "{\"name\":\"John\",\"age\":30}"
    //   mydbapp MyDatabase update record_1630512345.json "{\"name\":\"Jane\",\"age\":25}"
    // ----------------------------------------------------------------------
    
    // Verificar que se han pasado al menos 3 argumentos
    if (argc < 3) {
        std::cerr << "Usage:\n"
                  << "  " << argv[0] << " <databaseName> select\n"
                  << "  " << argv[0] << " <databaseName> insert <jsonData>\n"
                  << "  " << argv[0] << " <databaseName> update <fileName> <jsonData>\n";
        return 1;
    }

    // Extraer los argumentos de la línea de comandos
    std::string databaseName = argv[1];
    std::string operation    = argv[2];

    // Asegurarse de que la carpeta de la base de datos existe; si no, se crea
    try {
        std::filesystem::create_directories(databaseName);
    } catch (const std::exception &ex) {
        std::cerr << "Error creating/checking directory: " << ex.what() << '\n';
        return 1;
    }

    // ----------------------------------------------------------------------
    // OPERACIÓN SELECT: Listar todos los archivos JSON en la carpeta y mostrar su contenido
    // ----------------------------------------------------------------------
    if (operation == "select") {
        try {
            // Iterar por cada archivo en la carpeta de la base de datos
            for (const auto& entry : std::filesystem::directory_iterator(databaseName)) {
                if (entry.is_regular_file() && entry.path().extension() == ".json") {
                    // Leer el archivo
                    std::ifstream ifs(entry.path());
                    if (!ifs) {
                        std::cerr << "Error opening file: " << entry.path() << '\n';
                        continue;
                    }
                    std::string content((std::istreambuf_iterator<char>(ifs)),
                                         std::istreambuf_iterator<char>());
                    // Mostrar nombre y contenido del archivo
                    std::cout << "File: " << entry.path().filename().string() << '\n';
                    std::cout << "Content:\n" << content << "\n\n";
                }
            }
        } catch (const std::exception &ex) {
            std::cerr << "Error reading directory contents: " << ex.what() << '\n';
            return 1;
        }
    }
    // ----------------------------------------------------------------------
    // OPERACIÓN INSERT: Crear un nuevo registro en formato JSON
    // ----------------------------------------------------------------------
    else if (operation == "insert") {
        if (argc < 4) {
            std::cerr << "Error: Missing JSON data for insert operation.\n";
            return 1;
        }
        // El dato JSON a insertar
        std::string jsonData = argv[3];

        // Generar un nombre de archivo basado en el tiempo actual para garantizar unicidad
        auto now = std::chrono::system_clock::now();
        auto now_c = std::chrono::system_clock::to_time_t(now);
        std::string fileName = "record_" + std::to_string(now_c) + ".json";
        std::filesystem::path filePath = std::filesystem::path(databaseName) / fileName;

        // Escribir el JSON en el archivo
        try {
            std::ofstream ofs(filePath);
            if (!ofs) {
                std::cerr << "Error creating file: " << filePath.string() << '\n';
                return 1;
            }
            ofs << jsonData;
            ofs.close();
            std::cout << "Data inserted successfully into: " << filePath.string() << '\n';
        } catch (const std::exception &ex) {
            std::cerr << "Error writing file: " << ex.what() << '\n';
            return 1;
        }
    }
    // ----------------------------------------------------------------------
    // NUEVA FUNCIONALIDAD: OPERACIÓN UPDATE: Actualizar un registro existente
    // ----------------------------------------------------------------------
    else if (operation == "update") {
        // Se requiere el nombre del archivo y el nuevo JSON
        if (argc < 5) {
            std::cerr << "Error: Missing parameters for update operation. Usage:\n"
                      << "  " << argv[0] << " <databaseName> update <fileName> <jsonData>\n";
            return 1;
        }
        std::string fileName = argv[3];  // Nombre del archivo a actualizar
        std::string jsonData = argv[4];    // Nuevo JSON a insertar
        std::filesystem::path filePath = std::filesystem::path(databaseName) / fileName;

        // Verificar que el archivo exista antes de actualizarlo
        if (!std::filesystem::exists(filePath)) {
            std::cerr << "Error: File " << filePath.string() << " does not exist.\n";
            return 1;
        }

        // Escribir el nuevo JSON en el archivo (sobreescribe el contenido existente)
        try {
            std::ofstream ofs(filePath);
            if (!ofs) {
                std::cerr << "Error opening file for update: " << filePath.string() << '\n';
                return 1;
            }
            ofs << jsonData;
            ofs.close();
            std::cout << "Data updated successfully in: " << filePath.string() << '\n';
        } catch (const std::exception &ex) {
            std::cerr << "Error updating file: " << ex.what() << '\n';
            return 1;
        }
    }
    // ---------------------------------------------------------------------- 
    // OPERACIÓN DESCONOCIDA: Si la operación no es select, insert o update, se muestra un error.
    // ----------------------------------------------------------------------
    else {
        std::cerr << "Error: Unknown operation '" << operation << "'. Use 'select', 'insert' or 'update'.\n";
        return 1;
    }

    return 0;
}
