from minidb import *
from datetime import datetime
import json
import traceback

# Configuración global
NOMBRE_BASE_DATOS = "clientes"

def limpiar_registros_corruptos(conector: TinyDbConnector) -> None:
    """Elimina registros con formato inválido de la base de datos"""
    print("\n🔍 Iniciando limpieza de registros corruptos...")
    registros = conector.obtener_registros(parsear_json=False)
    corruptos = 0
    
    for registro in registros:
        if not isinstance(registro['contenido'], dict):
            print(f"  🗑️ Eliminando registro corrupto: {registro['archivo']}")
            conector.eliminar_registro(registro['archivo'])
            corruptos += 1
            
    print(f"✅ Limpieza completada: {corruptos} registros eliminados")

def validar_registro(registro: dict) -> bool:
    """Valida estructura y contenido del registro"""
    requeridos = {
        'nombre': (str, lambda x: 2 <= len(x) <= 50),  # Nombre entre 2-50 caracteres
        'edad': (int, lambda x: 1 <= x <= 120)         # Edad entre 18-120
    }
    
    try:
        return all(
            campo in registro and 
            isinstance(registro[campo], tipo) and 
            validador(registro[campo])
            for campo, (tipo, validador) in requeridos.items()
        )
    except Exception as e:
        print(f"⚠️ Error en validación: {str(e)}")
        return False

def mostrar_registro(registro: dict) -> None:
    """Muestra un registro con formato profesional"""
    if not isinstance(registro['contenido'], dict):
        print(f"\n⚠️ Registro corrupto: {registro['archivo']}")
        return
    
    contenido = registro['contenido']
    print(f"\n📄 ARCHIVO: {registro['archivo']}")
    print(f"├─ Nombre completo: {contenido.get('nombre', 'N/D')}")
    print(f"├─ Edad:            {contenido.get('edad', 'N/D')}")
    print(f"├─ Correo electrónico:  {contenido.get('email', 'N/D')}")
    print(f"├─ País:            {contenido.get('pais', 'N/D')}")
    print(f"├─ Fecha creación:  {contenido.get('creacion', 'N/D')}")
    print(f"└─ Última modificación: {contenido.get('actualizacion', 'N/D')}")
    print("─" * 60)

def main():
    try:
        # 1. Inicialización y limpieza
        print("\n" + "=" * 60)
        print("🚀 Iniciando Gestor de Base de Datos TinyDB".center(60))
        print("=" * 60)
        
        conector = TinyDbConnector(NOMBRE_BASE_DATOS)
        print(f"\n✅ Conexión exitosa a: '{NOMBRE_BASE_DATOS}'")
        
        limpiar_registros_corruptos(conector)
        
        # 2. Inserción de registros
        nuevos_registros = [
            {"nombre": "Juan Pérez", "edad": 30, "email": "juan@ejemplo.com"},
            {"nombre": "María García", "edad": 25, "pais": "España"},
            {"nombre": "Error", "edad": "treinta"},  # Inválido
            {"nombre": "A", "edad": 200}             # Inválido
        ]
        
        print("\n" + "📥 Proceso de Inserción de Registros ".ljust(60, "─"))
        
        for idx, registro in enumerate(nuevos_registros, 1):
            print(f"\nRegistro #{idx}:")
            
            if validar_registro(registro):
                # Añadir metadatos automáticos
                timestamp = datetime.now().isoformat()
                registro['creacion'] = timestamp
                registro['actualizacion'] = timestamp
                
                if conector.insertar_registro(registro):
                    print("  ✅ Insertado exitosamente!")
                    print(f"  📋 Contenido:\n{json.dumps(registro, indent=4, ensure_ascii=False)}")
                else:
                    print("  ❗ Error durante la inserción")
            else:
                print("  ❌ Registro inválido. Requisitos:")
                print("     - Nombre: 2-50 caracteres")
                print("     - Edad: entre 18 y 120 años")
        
        # 3. Análisis y visualización
        print("\n" + "📊 Análisis de Datos ".ljust(60, "─"))
        
        todos_registros = conector.obtener_registros()
        registros_validos = [r for r in todos_registros if isinstance(r['contenido'], dict)]
        
        print(f"Total registros: {len(todos_registros)}")
        print(f"Registros válidos: {len(registros_validos)}")
        print(f"Registros corruptos: {len(todos_registros) - len(registros_validos)}")
        
        # Estadísticas avanzadas
        if registros_validos:
            edades = [r['contenido'].get('edad', 0) for r in registros_validos]
            print("\n📈 Estadísticas:")
            print(f"  Edad promedio: {sum(edades)/len(edades):.1f} años")
            print(f"  Edad mínima:   {min(edades)} años")
            print(f"  Edad máxima:   {max(edades)} años")
        
        # 4. Búsqueda y visualización
        print("\n" + "🔍 Búsqueda de Registros ".ljust(60, "─"))
        
        termino_busqueda = "Javier"
        resultados = [
            r for r in registros_validos
            if termino_busqueda.lower() in r['contenido'].get('nombre', '').lower()
        ]
        
        print(f"\nBuscando: '{termino_busqueda}'")
        print(f"Resultados encontrados: {len(resultados)}")
        
        for registro in resultados:
            mostrar_registro(registro)
        
        # 5. Informe final
        print("\n" + "✅ Proceso Completado con Éxito ".center(60, "─"))
        stats = conector.obtener_estadisticas()
        print(f"\n📋 Resumen Final:")
        print(f"  Base de datos:   {stats['nombre']}")
        print(f"  Ubicación motor: {stats['ruta_motor']}")
        print(f"  Total registros: {stats['total_registros']}")
        
    except Exception as e:
        print("\n" + "❌ Error Crítico ".center(60, "!"))
        print(f"Mensaje: {str(e)}")
        print("\n🛠️ Detalles Técnicos:")
        traceback.print_exc()
        print("\n🔧 Acciones Recomendadas:")
        print("1. Verificar conexión con el motor de base de datos")
        print("2. Revisar formato de los registros existentes")
        print("3. Validar permisos de escritura en el directorio")
    finally:
        print("\n" + "🏁 Finalizando Ejecución ".center(60, "─"))

if __name__ == "__main__":
    main()