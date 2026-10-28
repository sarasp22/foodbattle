-- Attribue une photo d'illustration unique par plat (selon sa catégorie : burger, tacos, pizza)
-- uniquement si le plat n'a pas encore d'image personnalisée.
BEGIN;

-- Burgers (plats 1, 2, 7, 8)
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/1639557/pexels-photo-1639557.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 1 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/1633578/pexels-photo-1633578.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 2 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/1199957/pexels-photo-1199957.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 7 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/2983101/pexels-photo-2983101.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 8 AND image_url IS NULL;

-- Tacos (plats 3, 4, 9, 10)
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/4958792/pexels-photo-4958792.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 3 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/2092507/pexels-photo-2092507.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 4 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/461198/pexels-photo-461198.jpeg?auto=compress&cs=tinysrgb&w=800'  WHERE id = 9 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/7613568/pexels-photo-7613568.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 10 AND image_url IS NULL;

-- Pizzas (plats 5, 6, 11, 12)
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/315755/pexels-photo-315755.jpeg?auto=compress&cs=tinysrgb&w=800'   WHERE id = 5 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/825661/pexels-photo-825661.jpeg?auto=compress&cs=tinysrgb&w=800'   WHERE id = 6 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/1146760/pexels-photo-1146760.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 11 AND image_url IS NULL;
UPDATE dishes SET image_url = 'https://images.pexels.com/photos/2147491/pexels-photo-2147491.jpeg?auto=compress&cs=tinysrgb&w=800' WHERE id = 12 AND image_url IS NULL;

INSERT INTO schema_migrations (version)
VALUES ('005-distinct-dish-images')
ON CONFLICT (version) DO NOTHING;

COMMIT;
