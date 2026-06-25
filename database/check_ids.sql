SELECT 'users' as tbl, MAX(id) as max_id FROM users
UNION ALL SELECT 'products', MAX(id) FROM products
UNION ALL SELECT 'orders', MAX(id) FROM orders
UNION ALL SELECT 'order_items', MAX(id) FROM order_items
UNION ALL SELECT 'chat_sessions', MAX(id) FROM chat_sessions
UNION ALL SELECT 'chat_messages', MAX(id) FROM chat_messages;
