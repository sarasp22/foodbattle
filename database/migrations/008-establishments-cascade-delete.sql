-- Permet la suppression en cascade des plats lorsqu'un établissement est supprimé.

BEGIN;

ALTER TABLE dishes
DROP CONSTRAINT IF EXISTS dishes_establishment_id_fkey;

ALTER TABLE dishes
ADD CONSTRAINT dishes_establishment_id_fkey
FOREIGN KEY (establishment_id) REFERENCES establishments(id) ON DELETE CASCADE;

INSERT INTO schema_migrations (version)
VALUES ('008-establishments-cascade-delete')
ON CONFLICT (version) DO NOTHING;

COMMIT;
