import mysql.connector

class Persona:
    def __init__(self,
                    nuevonombre,
                    nuevosapellidos,
                    nuevaedad,nuevosemails):
        self.nombre = nuevonombre
        self.apellidos = nuevosapellidos
        self.edad = nuevaedad
        self.emails = nuevosemails

personas = []

correosjavier = [
        {
        'tipo':'personal',
        'valor':'info@horti.com'
        },
        {
        'tipo':'trabajo',
        'valor':['info@horti.com','horti2@gmail.com']
        }
    ]

personas.append(Persona("Javier","Hortigüela Valiente",46,['info@horti.com','info@horti.com']))
personas.append(Persona("Juan","Garcia",50,['juan@garcia.com','garcia@garcia.com']))


conexion = mysql.connector.connect(
            host='localhost',  
            database='accesoadatost2', 
            user='horti',  
            password='horti'  
        )
cursor = conexion.cursor()
for persona in personas:
    correos = ', '.join(persona.emails)
    peticion = f"""
                INSERT INTO personas
                VALUES (
                NULL,
                '{persona.nombre}',
                '{persona.apellidos}',
                {persona.edad},
                '{correos}'
                );
                """
    cursor.execute(peticion)
conexion.commit()
