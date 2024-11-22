class Persona:
    def __init__(self,
                    nuevonombre,
                    nuevosapellidos,
                    nuevaedad):
        self.nombre = nuevonombre
        self.apellidos = nuevosapellidos
        self.edad = nuevaedad

persona1 = Persona("Javier","Hortigüela Valiente",19)
persona2 = Persona("Juan","Menendez Cortan",50)

print(persona1)
