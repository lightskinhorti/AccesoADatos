import mysql.connector

class Persona:
    def __init__(self,
                    nuevonombre,
                    nuevosapellidos,
                    nuevaedad):
        self.nombre = nuevonombre
        self.apellidos = nuevosapellidos
        self.edad = nuevaedad

persona1 = Persona("javier","Hortigüela Valiente",23)
persona2 = Persona("Juan","Garcia",50)


conexion = mysql.connector.connect(
            host='localhost',  
            database='accesoadatost2', 
            user='horti',  
            password='horti'  
        )
cursor = conexion.cursor()

peticion = f"""
            INSERT INTO personas VALUES (NULL,'{persona1.nombre}','{persona1.apellidos}',{persona1.edad});
            """
cursor.execute(peticion)
conexion.commit()
