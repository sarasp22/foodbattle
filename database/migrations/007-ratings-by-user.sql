-- Associe chaque avis à un utilisateur.
-- Conserve les avis historiques sans leur attribuer arbitrairement un utilisateur.

BEGIN;

-- Ajoute l'identifiant de l'utilisateur.
ALTER TABLE ratings
ADD COLUMN user_id INTEGER;

-- Relie l'avis à un utilisateur existant.
ALTER TABLE ratings
ADD CONSTRAINT ratings_user_id_fkey
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Un utilisateur ne peut avoir qu'un avis en attente ou publié par plat.
CREATE UNIQUE INDEX ratings_user_dish_active_unique
ON ratings (user_id, dish_id)
WHERE status IN ('pending', 'published')
  AND user_id IS NOT NULL;

-- Enregistre la migration.
INSERT INTO schema_migrations (version)
VALUES ('007-ratings-by-user')
ON CONFLICT (version) DO NOTHING;

COMMIT;