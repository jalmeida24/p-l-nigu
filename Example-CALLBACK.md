```
curl -d '{"transaction":{"amount":"[REPLACE_WITH_AMOUNT]","kind":"capture","status":"success"}}' \
-X POST "[REPLACE_WITH_CLIENT_STORE_BASE_DOMAIN]/admin/api/2022-10/orders/[REPLACE_WITH_VALUE_OF_merchantTransactionId_WIDGET_FIELD]/transactions.json" \
-H "X-Shopify-Access-Token: [REPLACE_WITH_CLIENT_SHOPIFY_ACCESS_TOKEN]" \
-H "Content-Type: application/json"
```


### Need to replace tags

[REPLACE_WITH_AMOUNT] - amount paid

[REPLACE_WITH_CLIENT_STORE_BASE_DOMAIN] - Shopify base store url with http(s)

[REPLACE_WITH_VALUE_OF_merchantTransactionId_WIDGET_FIELD] - the widget unique payment identifier 

[REPLACE_WITH_CLIENT_SHOPIFY_ACCESS_TOKEN] - key generated at Shopify admin panel by client as described from step 8 onwards