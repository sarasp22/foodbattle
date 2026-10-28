-- Ajoute la colonne image_url (optionnelle) à la table dishes pour stocker la photo Cloudinary.
BEGIN;

ALTER TABLE dishes
ADD COLUMN IF NOT EXISTS image_url TEXT;

INSERT INTO schema_migrations (version)
VALUES ('003-add-dish-image-url')
ON CONFLICT (version) DO NOTHING;

COMMIT;
