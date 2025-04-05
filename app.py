from flask import Flask, render_template, request, session, jsonify
import hashlib
import os
import jwt
import datetime

# Создаем Flask-приложение
app = Flask(__name__, template_folder="templates")
app.secret_key = os.urandom(24)  # Генерируем секретный ключ

# Хранилище пользователей (в реальном проекте — база данных)
users = {}

# Функция хеширования пароля
def hash_password(password):
    return hashlib.sha256(password.encode()).hexdigest()

# Главная страница
@app.route('/')
def index():
    return render_template('index.html')

# Регистрация пользователя
@app.route('/register', methods=['POST'])
def register():
    login = os.urandom(5).hex()
    password = os.urandom(8).hex()
    hashed_password = hash_password(password)

    users[login] = hashed_password
    return jsonify({'login': login, 'password': password})

# Вход пользователя
@app.route('/login', methods=['POST'])
def login():
    data = request.json
    login = data.get('login')
    password = data.get('password')

    if login in users and users[login] == hash_password(password):
        session['user'] = login
        return jsonify({'message': 'Login successful', 'login': login})
    return jsonify({'error': 'Invalid credentials'}), 401

# Выход пользователя
@app.route('/logout', methods=['POST'])
def logout():
    session.pop('user', None)
    return jsonify({'message': 'Logged out'})

# Генерация JWT-токена
@app.route('/token', methods=['POST'])
def generate_token():
    if 'user' not in session:
        return jsonify({'error': 'Unauthorized'}), 401

    token = jwt.encode(
        {'user': session['user'], 'exp': datetime.datetime.utcnow() + datetime.timedelta(hours=1)},
        app.secret_key,
        algorithm="HS256"
    )
    return jsonify({'token': token})

# Запуск сервера
if __name__ == '__main__':
    app.run(host="0.0.0.0", port=5000)
