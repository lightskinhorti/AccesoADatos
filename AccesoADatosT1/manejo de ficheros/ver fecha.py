import os
from PIL import Image

# Ruta al directorio de imágenes
directorio_fondos = "FONDOS"

# Comprobamos si el directorio existe
if not os.path.exists(directorio_fondos):
    print(f"El directorio '{directorio_fondos}' no existe.")
else:
    # Listamos todos los archivos en el directorio
    lista_archivos = os.listdir(directorio_fondos)

    # Recorremos cada archivo en la lista
    for archivo in lista_archivos:
        # Construimos la ruta completa del archivo
        ruta_archivo = os.path.join(directorio_fondos, archivo)

        # Verificamos si es un archivo de imagen
        if os.path.isfile(ruta_archivo):
            try:
                # Abrimos la imagen
                imagen = Image.open(ruta_archivo)

                # Intentamos obtener los datos EXIF
                datosexif = imagen._getexif()

                # Verificamos si existen datos EXIF y si contiene la etiqueta 306
                if datosexif and 306 in datosexif:
                    cadena = datosexif[306].replace(":", "").replace(" ", "")
                    print(f"Archivo: {archivo}, Fecha de toma: {cadena}")
                else:
                    print(f"Archivo: {archivo}, No se encontraron datos EXIF de fecha.")
            
            except Exception as e:
                # Si ocurre un error al abrir la imagen o procesar los EXIF
                print(f"Error al procesar la imagen {archivo}: {e}")
