

class Persona:
    def __init__(self,
                    nuevonombre,
                    nuevosapellidos,
                    nuevaedad):
        self.nombre = nuevonombre
        self.apellidos = nuevosapellidos
        self.edad = nuevaedad

personas = []

personas.append(Persona("Javier","Hortigüela Valiente",46))
personas.append(Persona("Juan","Garcia",36))



