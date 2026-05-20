import asyncio
import asyncpg
import bcrypt
import os

async def main():
    con = await asyncpg.connect("postgresql://postgres:1234@localhost:5432/creating_software")
    
    # Генерируем хэш для пароля 1234
    salt = bcrypt.gensalt()
    password_hash = bcrypt.hashpw(b"1234", salt).decode('utf-8')
    
    # Обновляем пароли абсолютно для ВСЕХ пользователей
    await con.execute("UPDATE users SET password_hash = $1", password_hash)
    
    print("Успешно! Теперь у почты admin@example.com (и всех остальных) пароль: 1234")
    await con.close()

if __name__ == "__main__":
    asyncio.run(main())
