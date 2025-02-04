Serwach_OmnibusExtension

Module is based on sending special price via REST API.
After sending request like:

POST localhost/rest/all/V1/products/special-price
{
    "prices": [
    {
        "price": 9,
        "store_id": 0,
        "price_from": "2025-01-01 00:00:00",
        "price_to": "2025-01-31 00:00:00",
        "sku": "test"
    },
    {
        "price": 7,
        "store_id": 1,
        "price_from": "2025-01-01 00:00:00",
        "price_to": "2025-01-31 00:00:00",
        "sku": "test"
    }
    ]
}

You can see on frontend the attributes historical_price is updated together with special price.
After the sale is done historical_price is still saved on backend, but not visible on the frontend.
