import os
import re

def listar_estructura_markdown(ruta, archivo_salida):
    """
    Genera la estructura del directorio en formato Markdown con listas desordenadas.
    """
    with open(archivo_salida, 'w', encoding='utf-8') as f:
        f.write("# Estructura del Proyecto\n\n")
        for root, dirs, files in os.walk(ruta):
            relative_path = os.path.relpath(root, ruta)
            level = 0 if relative_path == '.' else relative_path.count(os.sep) + 1
            indent = '    ' * level
            carpeta = os.path.basename(root)
            if carpeta:
                f.write(f"{indent}- **{carpeta}/**\n")
            for file in files:
                f.write(f"{indent}    - {file}\n")

def extraer_docstring(file_path):
    """
    Extrae el docstring o comentarios iniciales de un archivo según su tipo.
    """
    _, ext = os.path.splitext(file_path)
    ext = ext.lower()
    doc = ""

    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        if ext == '.py':
            match = re.match(r'^\s*(?:\'\'\'|\"\"\")([\s\S]*?)(?:\'\'\'|\"\"\")', content, re.DOTALL)
            if match:
                doc = match.group(1).strip()
            else:
                comments = [line.lstrip("#").strip() for line in content.splitlines() if line.strip().startswith("#")]
                doc = "\n".join(comments)
        elif ext in ['.js', '.php', '.css']:
            match = re.match(r'^\s*/\*([\s\S]*?)\*/', content, re.DOTALL)
            doc = match.group(1).strip() if match else ""
        elif ext == '.html':
            match = re.match(r'^\s*<!--([\s\S]*?)-->', content, re.DOTALL)
            doc = match.group(1).strip() if match else ""
    except Exception as e:
        print(f"Error al procesar el archivo {file_path}: {e}")

    return doc

def agregar_docstrings_markdown(ruta, archivo_salida):
    """
    Agrega docstrings/comentarios de los archivos al documento Markdown.
    """
    with open(archivo_salida, 'a', encoding='utf-8') as f:
        f.write("\n# Documentación de Archivos\n\n")
        for root, dirs, files in os.walk(ruta):
            for file in files:
                file_path = os.path.join(root, file)
                doc = extraer_docstring(file_path)
                if doc:
                    relative_path = os.path.relpath(file_path, ruta)
                    f.write(f"## {relative_path}\n\n{doc}\n\n")

def main():
    """
    Función principal que ejecuta las dos fases: generación de estructura y extracción de docstrings.
    """
    carpeta = input("Indica la carpeta sobre la cual sacar la estructura: ").strip()
    if not os.path.isdir(carpeta):
        print(f"La ruta especificada '{carpeta}' no es una carpeta válida.")
        return
    
    archivo_md = 'estructura_proyecto.md'
    listar_estructura_markdown(carpeta, archivo_md)
    print(f"La estructura del proyecto ha sido guardada en '{archivo_md}'.")
    agregar_docstrings_markdown(carpeta, archivo_md)
    print(f"Las docstrings/comentarios han sido agregados al archivo '{archivo_md}'.")

if __name__ == "__main__":
    main()
