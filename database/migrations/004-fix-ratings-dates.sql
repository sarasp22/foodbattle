-- Attribue des dates échelonnées aux avis de démonstration existants (Gourmand3 à Gourmand38)
-- pour éviter qu'ils n'affichent tous la date d'initialisation du conteneur Docker.
BEGIN;

UPDATE ratings
SET created_at = CURRENT_TIMESTAMP - ((37 - id) * INTERVAL '1 day')
WHERE id BETWEEN 1 AND 36
  AND nickname LIKE 'Gourmand%';

INSERT INTO schema_migrations (version)
VALUES ('004-fix-ratings-dates')
ON CONFLICT (version) DO NOTHING;

COMMIT;
