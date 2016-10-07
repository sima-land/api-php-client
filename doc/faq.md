# Часто задаваемые вопросы

**1. Как получить фото товаров разных размеров?**

Поле `photos` сущности `item` содержит список базовых URL для получения фотографий. Если к базовому URL добавть имя файла желаемого размера то получим полный URL фотографии.

Например, базовый URL `https://st-sima.r.worldssl.net/items/1277617/0/`, имя файла `700.jpg`, полный URL `https://st-sima.r.worldssl.net/items/1277617/0/700.jpg`

Список всех возможный имен файлов можно получить по API [https://www.sima-land.ru/api/v3/photo-size/](https://www.sima-land.ru/api/v3/photo-size/)
