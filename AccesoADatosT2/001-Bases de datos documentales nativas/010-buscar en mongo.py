from pymongo import MongoClient

cliente = MongoClient("mongodb://localhost:27017/")     # Cambia la URL si tu MongoDB está en otra ubicación
basededatos = cliente["clientes"]                        # Reemplaza con el nombre de tu base de datos
coleccion = basededatos["clientes"]                     # Reemplaza con el nombre de tu colección

resultado = coleccion.find_one({"nombre": "pablo"})     # Reemplaza "campo" y "valor" por el criterio de búsqueda

if resultado:
    print("Documento encontrado:", resultado)
else:
    print("No se encontró ningún documento que coincida con el criterio.")
