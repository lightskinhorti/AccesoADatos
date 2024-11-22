import json

class Persona:
    def __init__(self,
                    nuevonombre,
                    nuevosapellidos,
                    nuevaedad,nuevosemails):
        self.nombre = nuevonombre
        self.apellidos = nuevosapellidos
        self.edad = nuevaedad
        self.emails = nuevosemails
    def to_dict(self):
        return {
            'nombre': self.nombre,
            'apellidos': self.apellidos,
            'edad': self.edad,
            'emails': self.emails
        }

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

personas.append(Persona("Javier","Hortigüela Valiente",46,correosjavier))

diccionario = [persona.to_dict() for persona in personas]


archivo =  open('personas.json', 'w', encoding='utf-8')
json.dump(diccionario, archivo, ensure_ascii=False, indent=4)
archivo.close()
