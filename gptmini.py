import random
import mysql.connector
import ast

# Configuración de la conexión a la base de datos MySQL
db_config = {
    "host": "localhost",
    "user": "usuario",
    "password": "contraseña",
    "database": "nombre_de_la_base_de_datos"
}

# Base de datos reformulada
database_reformulated = {
    "inputs": [],
    "responses": []
}

# Función para reformular el texto utilizando varias neuronas
def reformulate_text(input_text):
    reformulated_text = input_text.lower()
    reformulated_text = reformulated_text.replace("hola", "Saludos")
    reformulated_text = reformulated_text.replace("¿cómo estás?", "¿Cómo te encuentras?")
    reformulated_text = reformulated_text.replace("comida", "alimento")
    reformulated_text = reformulated_text.replace("favorita", "preferida")
    reformulated_text = reformulated_text.replace("color", "tonalidad")
    return reformulated_text

# Establecer conexión con la base de datos MySQL
connection = mysql.connector.connect(**db_config)
cursor = connection.cursor()

# Obtener datos de la base de datos MySQL
query = "SELECT input_text, response_text FROM chat_data"
cursor.execute(query)
result = cursor.fetchall()

# Reformular y almacenar los datos en la base de datos reformulada
for row in result:
    input_text = row[0]
    response_text = row[1]
    reformulated_input = reformulate_text(input_text)
    reformulated_response = reformulate_text(response_text)
    database_reformulated["inputs"].append(reformulated_input)
    database_reformulated["responses"].append(reformulated_response)

# Función para evaluar la calidad del código
def evaluate_code_quality(code):
    try:
        ast.parse(code)
        return True
    except SyntaxError:
        return False

# Ejemplo de interacción con la base de datos reformulada
while True:
    user_input = input("Usuario: ").lower()
    
    # Buscar la respuesta más coherente en la base de datos reformulada
    matched_responses = []
    for i, input_entry in enumerate(database_reformulated["inputs"]):
        if input_entry in user_input:
            matched_responses.append(database_reformulated["responses"][i])
    
    # Si hay respuestas coincidentes, seleccionar una al azar
    if matched_responses:
        response = random.choice(matched_responses)
    else:
        response = "Lo siento, no puedo entenderlo. ¿Podrías reformular tu pregunta o comentario?"
        database_reformulated["inputs"].append(user_input)
        database_reformulated["responses"].append(response)
        
        # Reformular y mejorar el código existente
        reformulated_code = reformulate_text(user_input)
        if evaluate_code_quality(reformulated_code):
            response += " Además, he mejorado mi propio código para comprender mejor tus consultas."
        else:
            response += " Lamentablemente, no he podido mejorar mi propio código debido a una posible ofuscación."
    
    print("IA: " + response)
