-- Associe chaque vote à un utilisateur connecté.
-- Conserve les votes existants sans leur attribuer arbitrairement un utilisateur.

BEGIN;

-- Ajoute l'identifiant de l'utilisateur.
ALTER TABLE votes
ADD COLUMN user_id INTEGER;

-- Relie le vote à un utilisateur.
ALTER TABLE votes
ADD CONSTRAINT votes_user_id_fkey
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Autorise les anciens votes à conserver leur visitor_key,
-- tout en permettant aux nouveaux votes de ne plus l'utiliser.
ALTER TABLE votes
ALTER COLUMN visitor_key DROP NOT NULL;

-- Supprime l'ancienne unicité basée sur visitor_key.
ALTER TABLE votes
DROP CONSTRAINT votes_dish_visitor_unique;

-- Un utilisateur ne peut voter qu'une fois pour un même plat.
ALTER TABLE votes
ADD CONSTRAINT votes_user_dish_unique UNIQUE (user_id, dish_id);

-- Enregistre la migration.
INSERT INTO schema_migrations (version)
VALUES ('006-votes-by-user')
ON CONFLICT (version) DO NOTHING;

COMMIT;
