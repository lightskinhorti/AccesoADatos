import requests

# Define the endpoint URL
url = "http://localhost:8080/accesoadatost3/API/012-reorganizar/api.php"  # Replace with your actual endpoint

try:
    # Make a GET request to fetch the data
    response = requests.get(url)
    response.raise_for_status()  # Raise an error for HTTP errors (4xx, 5xx)

    # Parse the JSON response into Python objects
    data = response.json()

    # Structure the data into a dictionary
    structured_data = {}
    for entry in data:
        cliente = entry["cliente"]
        nombre_completo = f"{cliente['nombre']} {cliente['apellidos']}"
        pedidos = entry.get("pedidos", [])
        pedido_list = [
            {
                "fecha": pedido["fecha"],
                "lineas_pedido": [
                    {"producto": lp["producto"], "cantidad": lp["cantidad"]}
                    for lp in pedido.get("lineaspedido", [])
                ],
            }
            for pedido in pedidos
        ]
        structured_data[nombre_completo] = pedido_list

    # Print the structured dictionary
    print(structured_data)

except requests.RequestException as e:
    print(f"An error occurred while making the request: {e}")
except ValueError as e:
    print(f"An error occurred while parsing the JSON: {e}")
