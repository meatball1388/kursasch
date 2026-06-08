import asyncio
import asyncpg
import bcrypt
import os
import dotenv

dotenv.load_dotenv()

async def fix():
    db_url = os.getenv("DB_URL")
    if not db_url:
        print("ОШИБКА: DB_URL не найден в .env")
        return
        
    con = await asyncpg.connect(db_url)
    salt = bcrypt.gensalt()
    # Генерируем хэш для пароля 1234
    password_hash = bcrypt.hashpw(b"1234", salt).decode('utf-8')
    
    await con.execute("UPDATE users SET password_hash = $1, salt = $2", password_hash, salt.decode('utf-8'))
    await con.close()
    print("Успешно! Теперь у всех пользователей пароль: 1234")

if __name__ == "__main__":
    asyncio.run(fix())
