#!/usr/bin/env python3
import tkinter as tk
from tkinter import messagebox
import subprocess
import json

def execute_db_command(command_args):
    """
    Ejecuta un comando en la base de datos utilizando el módulo subprocess.
    
    :param command_args: Lista de argumentos del comando a ejecutar.
    :return: Tupla (codigo_retorno, salida, error)
    """
    try:
        resultado = subprocess.run(command_args, capture_output=True, text=True)
        return resultado.returncode, resultado.stdout, resultado.stderr
    except Exception as e:
        return -1, "", str(e)

def insert_data():
    """Recoge el nombre y la edad desde los campos de entrada y los inserta como JSON."""
    nombre = name_entry.get().strip()
    edad = age_entry.get().strip()
    
    if not nombre or not edad.isdigit():
        messagebox.showerror("Error", "Por favor, ingrese un nombre y una edad válida.")
        return
    
    json_datos = json.dumps({"nombre": nombre, "edad": int(edad)})
    comando_insertar = ["mydbapp.exe", "clientes", "insert", json_datos]
    codigo_retorno, salida, error = execute_db_command(comando_insertar)
    
    if codigo_retorno == 0:
        messagebox.showinfo("Éxito", f"Datos insertados: {json_datos}")
    else:
        messagebox.showerror("Error al insertar", error)

def retrieve_data():
    """Recupera todos los datos desde la base de datos y los muestra en la interfaz gráfica."""
    comando_seleccionar = ["mydbapp.exe", "clientes", "select"]
    codigo_retorno, salida, error = execute_db_command(comando_seleccionar)
    
    if codigo_retorno == 0:
        results_text.delete("1.0", tk.END)
        results_text.insert(tk.END, salida)
    else:
        messagebox.showerror("Error al recuperar datos", error)

def update_data():
    """Actualiza un registro en la base de datos permitiendo modificar todo el JSON."""
    file_name = file_entry.get().strip()  # Obtener el nombre del archivo a actualizar
    nombre = name_entry.get().strip()
    edad = age_entry.get().strip()
    
    if not file_name or not nombre or not edad.isdigit():
        messagebox.showerror("Error", "Ingrese un archivo, nombre y edad válida para actualizar.")
        return
    
    nuevo_json = json.dumps({"nombre": nombre, "edad": int(edad)})
    comando_actualizar = ["mydbapp.exe", "clientes", "update", file_name, nuevo_json]
    codigo_retorno, salida, error = execute_db_command(comando_actualizar)
    
    if codigo_retorno == 0:
        messagebox.showinfo("Éxito", f"Registro actualizado: {nuevo_json}")
    else:
        messagebox.showerror("Error al actualizar", error)

# Crear la ventana principal
root = tk.Tk()
root.title("Gestor de Base de Datos JSON")

# Etiquetas y campos de entrada
tk.Label(root, text="Archivo:").grid(row=0, column=0, sticky="w", padx=5, pady=5)
file_entry = tk.Entry(root, width=30)
file_entry.grid(row=0, column=1, padx=5, pady=5)

tk.Label(root, text="Nombre:").grid(row=1, column=0, sticky="w", padx=5, pady=5)
name_entry = tk.Entry(root, width=30)
name_entry.grid(row=1, column=1, padx=5, pady=5)

tk.Label(root, text="Edad:").grid(row=2, column=0, sticky="w", padx=5, pady=5)
age_entry = tk.Entry(root, width=30)
age_entry.grid(row=2, column=1, padx=5, pady=5)

# Botones
insert_btn = tk.Button(root, text="Insertar", command=insert_data)
insert_btn.grid(row=3, column=0, padx=5, pady=10, sticky="ew")

retrieve_btn = tk.Button(root, text="Listar", command=retrieve_data)
retrieve_btn.grid(row=3, column=1, padx=5, pady=10, sticky="ew")

update_btn = tk.Button(root, text="Actualizar", command=update_data)
update_btn.grid(row=4, column=0, columnspan=2, padx=5, pady=10, sticky="ew")

# Área de texto para mostrar resultados
results_text = tk.Text(root, width=60, height=10)
results_text.grid(row=5, column=0, columnspan=2, padx=5, pady=10)

root.mainloop()
