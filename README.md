### Daily limit exceeded for command

This limitation happens with our domain name provider internetbs.net

__Proposed solution__

1. Consume the CRM API not the registra's to get expiring.
2. To achive this we must introduce a meta field to the products UI and database.

3. Introduce route for products expiring.
    __Route__
    products/expire

    __HTTP Methods__
    1. GET

    __Arguments__
    1. Days
    2. Page
    3. Per page