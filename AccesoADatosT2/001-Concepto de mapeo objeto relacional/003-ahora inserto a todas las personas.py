import mysql.connector

class Persona:
    def __init__(self,
                    nuevonombre,
                    nuevosapellidos,
                    nuevaedad):
        self.nombre = nuevonombre
        self.apellidos = nuevosapellidos
        self.edad = nuevaedad

personas = []
persona1 = Persona("carlos alejandro","Sanchez",21)
personas.append(Persona("alberto","Garcia",50))


conexion = mysql.connector.connect(
            host='localhost',  
            database='accesoadatost2', 
            user='horti',  
            password='horti'  
        )
cursor = conexion.cursor()
for persona in personas:
    peticion = f"""
                INSERT INTO personas VALUES (NULL,'{persona.nombre}','{persona.apellidos}',{persona.edad});
                """
    cursor.execute(peticion)
conexion.commit()
