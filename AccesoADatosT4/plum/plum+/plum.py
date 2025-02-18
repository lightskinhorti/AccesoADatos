#!/usr/bin/env python3

import os
import json

def get_size(path):
    """Calcula el tamaño de un archivo o directorio."""
    try:
        if os.path.isfile(path):
            return os.path.getsize(path)
        elif os.path.isdir(path):
            total_size = 0
            for dirpath, dirnames, filenames in os.walk(path):
                for f in filenames:
                    fp = os.path.join(dirpath, f)
                    if not os.path.islink(fp):  # Ignorar enlaces simbólicos rotos
                        total_size += os.path.getsize(fp)
            return total_size
    except (OSError, PermissionError) as e:
        print(f"Error accediendo a {path}: {str(e)}")
        return None

def list_files_and_folders_recursive(root, folders_only=False):
    """
    Lista recursivamente archivos y directorios.
    
    Devuelve una lista de diccionarios con:
      - name: Nombre del elemento
      - path: Ruta completa
      - type: Tipo (file/directory)
      - size: Tamaño en bytes
      - contents: Contenidos (solo directorios)
    """
    structure = []
    try:
        for entry in os.scandir(root):
            if entry.is_dir():
                structure.append({
                    'name': entry.name,
                    'path': entry.path,
                    'type': 'directory',
                    'size': get_size(entry.path),
                    'contents': list_files_and_folders_recursive(entry.path, folders_only)
                })
            elif not folders_only:
                structure.append({
                    'name': entry.name,
                    'path': entry.path,
                    'type': 'file',
                    'size': get_size(entry.path)
                })
    except (OSError, PermissionError) as e:
        print(f"Error escaneando {root}: {str(e)}")
    return structure

def transform_for_d3_sunburst(data, parent_name="root"):
    """
    Transforma la estructura de datos al formato requerido por D3 sunburst.
    
    Cada nodo tendrá:
      - name: Nombre/ruta
      - children: Subelementos
      - value: Tamaño (solo archivos)
    """
    node = {
        "name": parent_name,
        "children": []
    }
    
    for item in data:
        size = item.get('size', 0) or 0
        if 'contents' in item and item['contents']:
            children_node = transform_for_d3_sunburst(item['contents'], item['name'])
            node["children"].append(children_node)
        else:
            node["children"].append({
                "name": item['name'],
                "value": size
            })
    
    return node

def create_html_sunburst_chart(data, html_file):
    """
    Crea el HTML final con el gráfico interactivo.
    """
    json_data = json.dumps(data)

    html_content = f"""\
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Zoomable Sunburst Chart</title>
<style>
    /* Estilos CSS existentes... */
</style>
</head>
<body>

<div class="center-container">
  <!-- LOGIN FORM -->
  <div class="login-card" id="login-card">
    <h1>Login</h1>
    <label for="username">Username</label>
    <input type="text" id="username" placeholder="Username" />
    <label for="password">Password</label>
    <input type="password" id="password" placeholder="Password" />
    <button onclick="attemptLogin()">Sign In</button>
    <div class="error-msg" id="error-msg"></div>
  </div>

  <!-- CHART CONTAINER (hidden until login) -->
  <div id="chart-container">
    <div class="chart-title">Zoomable Sunburst Chart</div>
    <div class="chart-controls">
      <button onclick="goParent()">Go to Parent Folder</button>
      <button onclick="goRoot()">Go to Root Folder</button>
    </div>
    <div class="chart">
      <svg id="sunburst" width="800" height="800"></svg>
    </div>
  </div>
</div>

<div class="tooltip" id="tooltip"></div>

<!-- D3.js v7 from CDN -->
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
    const VALID_USER = "javier";
    const VALID_PASS = "javier";

    let root;              // the root of our hierarchy
    let currentNode;       // which node we're zoomed in on
    let arcPaths;          // reference to all arcs
    let g;                 // the 'g' container for arcs
    let arc;               // arc generator
    let svg;               // reference to the svg
    const radius = 400;    // half of 800
    let partition;         // d3 partition

    // Our hierarchical data from Python
    const data = {json_data};

    function attemptLogin() {{
        const userField = document.getElementById("username");
        const passField = document.getElementById("password");
        const errMsg    = document.getElementById("error-msg");

        if (userField.value === VALID_USER && passField.value === VALID_PASS) {{
            document.getElementById("login-card").style.display = "none";
            document.getElementById("chart-container").style.display = "block";
            initSunburst(); // initialize chart
        }} else {{
            errMsg.textContent = "Invalid username or password";
        }}
    }}

    function initSunburst() {{
        const width = 800;
        const format = d3.format(",d");

        svg = d3.select("#sunburst")
            .attr("viewBox", [0, 0, width, width])
            .style("font", "12px sans-serif");

        g = svg.append("g")
            .attr("transform", "translate(" + (width / 2) + "," + (width / 2) + ")");

        root = d3.hierarchy(data)
            .sum(d => d.value || 0)
            .sort((a, b) => b.value - a.value);

        // Create a partition layout for the entire circle (2π, radius)
        partition = d3.partition().size([2 * Math.PI, radius]);

        partition(root);

        // We'll store the initial layout in d.current
        root.each(d => d.current = d);

        // Color scale
        const color = d3.scaleOrdinal(d3.quantize(d3.interpolateRainbow, root.children.length + 1));

        // Arc generator (with a bit of padding)
        arc = d3.arc()
            .startAngle(d => d.x0)
            .endAngle(d => d.x1)
            .padAngle(d => Math.min((d.x1 - d.x0) / 2, 0.005))
            .padRadius(radius / 2)
            .innerRadius(d => d.y0)
            .outerRadius(d => d.y1 - 1);

        const tooltip = d3.select("#tooltip");

        // Draw arcs
        arcPaths = g.selectAll("path")
            .data(root.descendants())
            .join("path")
                .attr("d", d => arc(d.current))
                .attr("fill", d => {{
                    // Color by top-level parent
                    while (d.depth > 1) d = d.parent;
                    return color(d.data.name);
                }})
                .attr("fill-opacity", d => arcVisible(d.current) ? 0.8 : 0) // fade out hidden arcs
                .on("mouseover", function(event, d) {{
                    tooltip
                        .style("opacity", 1)
                        .html(() => {{
                            const sizeStr = d.value > 0 ? format(d.value) + " bytes" : "0 bytes";
                            return "<strong>" + d.data.name + "</strong><br/>" + sizeStr;
                        }})
                        .style("left", (event.pageX + 10) + "px")
                        .style("top", (event.pageY - 28) + "px");

                    d3.select(this)
                        .attr("stroke", "#000")
                        .attr("stroke-width", 1);
                }})
                .on("mousemove", function(event) {{
                    tooltip
                        .style("left", (event.pageX + 10) + "px")
                        .style("top", (event.pageY - 28) + "px");
                }})
                .on("mouseout", function() {{
                    tooltip.style("opacity", 0);
                    d3.select(this)
                        .attr("stroke", null)
                        .attr("stroke-width", null);
                }})
                .on("click", clicked);

        // Only show pointer if the node has children (directory)
        arcPaths.filter(d => d.children).style("cursor", "pointer");

        // Set current node to root initially
        currentNode = root;

        // Expose the "clicked" function globally so buttons can call it
        window.clicked = clicked;
    }}

    function arcVisible(d) {{
        // A node is visible if it’s within the outer radius
        return d.y1 <= radius && d.y0 >= 0 && d.x1 > d.x0;
    }}

    // The core "zoom" function. On click, re-map angles so the clicked node
    // fills the entire circle from 0..2π
    function clicked(event, p) {{
        if (p === currentNode) return; // do nothing if same node

        currentNode = p;

        // Remap each node's angles from [p.x0..p.x1] into [0..2π]
        root.each(d => {{
            const x0 = (d.x0 - p.x0) / (p.x1 - p.x0) * 2 * Math.PI;
            const x1 = (d.x1 - p.x0) / (p.x1 - p.x0) * 2 * Math.PI;

            d.target = {{
                x0: x0 < 0 ? 0 : x0,
                x1: x1 > 2 * Math.PI ? 2 * Math.PI : x1,
                y0: Math.max(0, d.y0 - p.depth),
                y1: Math.max(0, d.y1 - p.depth)
            }};
        }});

        const t = g.transition().duration(750);

        // Transition arcs to their new angles
        arcPaths.transition(t)
            .tween("data", d => {{
                const i = d3.interpolate(d.current, d.target);
                return t => d.current = i(t);
            }})
            .attrTween("d", d => () => arc(d.current))
            .attr("fill-opacity", d => arcVisible(d.target) ? 0.8 : 0);
    }}

    // Zoom out to parent
    function goParent() {{
        if (!currentNode || !currentNode.parent) return;
        // Simulate a click on the parent
        window.clicked(new Event("click"), currentNode.parent);
    }}

    // Zoom out to root
    function goRoot() {{
        if (!root) return;
        window.clicked(new Event("click"), root);
    }}
</script>
</body>
</html>
"""

    with open(html_file, "w", encoding="utf-8") as f:
        f.write(html_content)

def main():
    # Cambia esto al directorio que deseas escanear
    root_dir = r"C:\Users\jhortiguela\Videos"
    output_file = "file_structure.json"
    html_file = "file_structure_sunburst.html"

    if not os.path.exists(root_dir):
        print(f"Error: El directorio {root_dir} no existe!")
        return

    print("Escaneando directorio...")
    file_structure = list_files_and_folders_recursive(root_dir)

    print("Transformando datos para D3 sunburst...")
    d3_data = transform_for_d3_sunburst(file_structure, parent_name=os.path.basename(root_dir))

    print(f"Creando gráfico interactivo en {html_file}...")
    create_html_sunburst_chart(d3_data, html_file)

    print("¡Proceso completado!")

if __name__ == "__main__":
    main()