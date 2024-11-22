import pickle

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

personas.append(Persona("Javier","Hortigüela Valiente",46,correosjavier))

archivo = open('binario.bin', 'wb')
pickle.dump(personas, archivo)
archivo.close()



