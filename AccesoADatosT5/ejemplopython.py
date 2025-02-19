#!/usr/bin/env python3

import subprocess
import json

def execute_db_command(command_args):
    """
    Ejecuta un comando en la base de datos utilizando el módulo subprocess.
    
    :param command_args: Lista de argumentos del comando a ejecutar.
    :return: Tupla (codigo_retorno, salida, error)
    """
    resultado = subprocess.run(command_args, capture_output=True, text=True)
    return resultado.returncode, resultado.stdout, resultado.stderr

def main():
    # Definiendo los datos en formato JSON que se insertarán en la base de datos
    datos = {"nombre": "maria", "edad": 69}
    json_datos = json.dumps(datos)  # Convertir el diccionario a una cadena JSON
    
    print("Insertando datos...")
    comando_insertar = ["mydbapp.exe", "clientes", "insert", json_datos]
    codigo_retorno, salida, error = execute_db_command(comando_insertar)
    
    if codigo_retorno != 0:
        print("Error al insertar datos:", error)
        return
    else:
        print("Datos insertados correctamente:")
        print(salida)
    
    print("\nRecuperando datos...")
    comando_seleccionar = ["mydbapp.exe", "clientes", "select"]
    codigo_retorno, salida, error = execute_db_command(comando_seleccionar)
    
    if codigo_retorno != 0:
        print("Error al recuperar datos:", error)
        return
    else:
        print("Datos recuperados correctamente:")
        print(salida)
    
    # Nueva característica: Actualización de un cliente por nombre
    print("\nActualizando cliente...")
    datos_actualizados = {"nombre": "juan antonio", "edad": 45}  # json actualizado
    json_datos_actualizados = json.dumps(datos_actualizados)
    comando_actualizar = ["mydbapp.exe", "clientes", "update", "record_1739979841.json", json_datos_actualizados] #nombre programa, nombre base datos, funcion que realizar, en este caso como es actualizar, el archivo que queremos actualizar y por ultimo el json actualizado
    codigo_retorno, salida, error = execute_db_command(comando_actualizar)
    
    if codigo_retorno != 0:
        print("Error al actualizar cliente:", error)
        return
    else:
        print("Cliente actualizado correctamente:")
        print(salida)

if __name__ == "__main__":
    main()
