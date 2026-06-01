from datetime import datetime
from typing import Optional
from pydantic import BaseModel

'''Table bookings {
  id integer [primary key]
  user_id integer
  resource_id integer
  start_time timestamp
  end_time timestamp
  status varchar // CREATED, CONFIRMED, PAID, CANCELLED
  price decimal
  created_at timestamp
  comment text
  adults integer
  children integer
  name varchar
  email varchar
  phone varchar
  passport text
}'''

class Booking(BaseModel):
    id: int
    user_id: int
    resource_id: int
    start_time: datetime
    end_time: datetime
    status: str
    price: float
    created_at: datetime
    comment: Optional[str] = None
    adults: int = 0
    children: int = 0
    name: Optional[str] = None
    email: Optional[str] = None
    phone: Optional[str] = None
    passport: Optional[str] = None
