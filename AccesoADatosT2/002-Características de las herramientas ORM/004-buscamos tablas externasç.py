import mysql.connector  # Importa el conector MySQL para conectar con la base de datos

###################################### CREO UNA CLASE QUE ES EL MODELO DE DATOS
class Producto:
    def __init__(self):
        self.nombre = None           # Inicializa el atributo 'nombre'
        self.descripcion = None      # Inicializa el atributo 'descripcion'
        self.precio = None           # Inicializa el atributo 'precio'
        self.categorias = None       # Inicializa el atributo 'categorias'

clase = "Producto"                   # Define el nombre de la clase como un string

##################################### PREPARO UNA CONEXIÓN CON EL SERVIDOR

# Configura la conexión con la base de datos
conexion = mysql.connector.connect(
            host='localhost',          # Dirección del servidor MySQL
            database='accesoadatost2',   # Nombre de la base de datos
            user='horti',              # Usuario de la base de datos
            password='horti'            # Contraseña del usuario
        )

cursor = conexion.cursor(dictionary=True)  # Crea un cursor que devuelve resultados como diccionarios

##################################### CREO UNA LISTA DE PRODUCTOS DE LA BASE DE DATOS

productos = []  # Inicializa una lista vacía para almacenar los productos recuperados

# Consulta SQL para seleccionar todos los registros de la tabla 'Producto'
peticion = "SELECT * FROM " + clase  
cursor.execute(peticion)                                            # Ejecuta la consulta SQL

                                                            # Recupera todas las filas resultantes de la consulta
filas = cursor.fetchall()
for fila in filas:                                          # Itera sobre cada fila (registro) de la consulta
    producto = Producto()                                   # Crea una nueva instancia de la clase 'Producto'
    
                                                            # Asigna cada campo del registro a un atributo del objeto 'producto'
    for clave, valor in fila.items():
        setattr(producto, clave, valor)                     # Usa 'setattr' para asignar dinámicamente valores
    
    productos.append(producto)                              # Añade el objeto 'producto' a la lista de productos
    
                                                            # Verifica si hay algún atributo sin valor (posible relación con otra tabla)
    for clave, valor in vars(producto).items():
        if valor == None:                                   # Si el valor es 'None'
            print("parece que hay una tabla externa en :", clave)  # Indica una posible relación externa

                                        # Imprime el diccionario de atributos del primer producto en la lista
print(vars(productos[0]))

                            # Cierra la transacción (no necesario aquí porque no hubo cambios, pero buena práctica)
conexion.commit()


















