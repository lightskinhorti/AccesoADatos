import subprocess
import json
import os
import sys
import shlex
from typing import Dict, List, Optional, Union
from pathlib import Path

class TinyDbConnector:
    """
    Conector seguro para bases de datos TinyDB que interactúa con un motor en C++.
    
    Características principales:
    - Operaciones CRUD (Crear, Leer, Actualizar, Eliminar)
    - Parsing inteligente de resultados
    - Manejo robusto de errores
    - Seguridad contra inyecciones de comandos
    """

    def __init__(self, db_name: str, engine_path: str = "mydbapp.exe"):
        """
        Inicializa el conector con validación de componentes.

        Parámetros:
        :param db_name:     Nombre de la base de datos (se usará como directorio)
        :param engine_path: Ruta al ejecutable del motor de base de datos
        """
        self.db_name = db_name
        self.engine_path = Path(engine_path)
        
        # Validación crítica: El motor debe existir
        if not self.engine_path.is_file():
            raise FileNotFoundError(f"Motor de base de datos no encontrado en: {self.engine_path}")

    def _execute_command(self, operation: str, data: Optional[Union[dict, str]] = None) -> subprocess.CompletedProcess:
        """
        Ejecuta comandos en el motor de base de datos de forma segura.

        :param operation: Operación a ejecutar (insert/update/select/delete)
        :param data:      Datos a enviar al motor (opcional)
        :return:          Resultado del subproceso
        """
        # Construcción segura del comando
        comando_base = [str(self.engine_path), self.db_name, operation]
        
        if data:
            # Serialización segura de datos
            json_str = json.dumps(data, ensure_ascii=False) if isinstance(data, dict) else str(data)
            comando_base.append(shlex.quote(json_str))  # Prevención de inyecciones
        
        try:
            resultado = subprocess.run(
                comando_base,
                capture_output=True,
                text=True,
                check=False,
                timeout=10  # Evita procesos colgados
            )
        except subprocess.TimeoutExpired:
            raise RuntimeError("Tiempo de espera agotado para la operación")
            
        return resultado

    def insertar_registro(self, datos: Dict) -> bool:
        """
        Inserta un nuevo registro en la base de datos.

        :param datos: Diccionario con datos a insertar
        :return:      True si la operación fue exitosa
        """
        if not isinstance(datos, dict):
            raise ValueError("Los datos deben ser un diccionario")
            
        resultado = self._execute_command("insert", datos)
        
        if resultado.returncode != 0:
            print(f"Error en inserción: {resultado.stderr.strip()}")
            return False
            
        print(f"Registro insertado: {resultado.stdout.strip()}")
        return True

    def actualizar_registro(self, archivo: str, nuevos_datos: Dict) -> bool:
        """
        Actualiza un registro existente.

        :param archivo:      Nombre del archivo a actualizar
        :param nuevos_datos: Datos a modificar/agregar
        :return:             True si la actualización fue exitosa
        """
        if not archivo.endswith(".json"):
            archivo += ".json"
            
        payload = {"archivo": archivo, "datos": nuevos_datos}
        resultado = self._execute_command("update", payload)
        
        if resultado.returncode != 0:
            print(f"Error en actualización: {resultado.stderr.strip()}")
            return False
            
        print(f"Registro actualizado: {resultado.stdout.strip()}")
        return True

    def obtener_registros(self, parsear_json: bool = True) -> List[Dict]:
        """
        Obtiene todos los registros de la base de datos.

        :param parsear_json: Intenta convertir el contenido a JSON
        :return:             Lista de registros con metadatos
        """
        resultado = self._execute_command("select")
        
        if resultado.returncode != 0:
            print(f"Error en consulta: {resultado.stderr.strip()}")
            return []
            
        return self._parsear_salida(resultado.stdout, parsear_json)

    # Nueva funcionalidad: Eliminación de registros
    def eliminar_registro(self, archivo: str) -> bool:
        """
        Elimina un registro específico de la base de datos.

        :param archivo: Nombre del archivo a eliminar
        :return:        True si la eliminación fue exitosa
        """
        if not archivo.endswith(".json"):
            archivo += ".json"
            
        resultado = self._execute_command("delete", {"archivo": archivo})
        
        if resultado.returncode != 0:
            print(f"Error en eliminación: {resultado.stderr.strip()}")
            return False
            
        print(f"Registro eliminado: {resultado.stdout.strip()}")
        return True

    def _parsear_salida(self, salida_cruda: str, parsear_json: bool) -> List[Dict]:
        """
        Convierte la salida del comando select en registros estructurados.

        :param salida_cruda:  Texto crudo de salida del motor
        :param parsear_json:  Intenta convertir contenido a JSON
        :return:              Lista de registros procesados
        """
        registros = []
        registro_actual = None
        buffer_contenido = []
        
        for linea in salida_cruda.splitlines():
            linea = linea.strip()
            
            # Detección de nuevo registro
            if linea.startswith("File: "):
                if registro_actual:
                    self._finalizar_registro(registro_actual, buffer_contenido, parsear_json, registros)
                registro_actual = {"archivo": linea[6:].strip()}
                buffer_contenido = []
            elif linea.startswith("Content:"):
                buffer_contenido.extend(linea[8:].strip())  # Contenido después de "Content:"
            elif registro_actual:
                buffer_contenido.append(linea)
        
        # Procesar último registro
        if registro_actual:
            self._finalizar_registro(registro_actual, buffer_contenido, parsear_json, registros)
            
        return registros

    def _finalizar_registro(self, registro: Dict, buffer: List[str], parsear_json: bool, registros: List[Dict]):
        """Procesa y almacena un registro completo"""
        try:
            contenido = "\n".join(buffer).strip()
            registro["contenido"] = json.loads(contenido) if parsear_json else contenido
            registros.append(registro)
        except json.JSONDecodeError:
            registro["contenido"] = f"JSON inválido: {contenido}"
            registros.append(registro)
            print(f"Advertencia: Error parseando JSON en {registro['archivo']}")

    def obtener_estadisticas(self) -> Dict:
        """
        Nueva funcionalidad: Obtiene estadísticas clave de la base de datos

        :return: Diccionario con:
                 - nombre: Nombre de la base de datos
                 - ruta_motor: Ruta del ejecutable
                 - total_registros: Cantidad de registros
        """
        return {
            "nombre": self.db_name,
            "ruta_motor": str(self.engine_path),
            "total_registros": len(self.obtener_registros())
        }