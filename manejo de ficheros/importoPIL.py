import os
import PIL.Image

lista = os.listdir("FONDOS")

for archivo in lista:
    print(archivo)
    imagen = PIL.Image.open('FONDOS/'+archivo)
    datosexif = imagen._getexif()
    print(datosexif)
