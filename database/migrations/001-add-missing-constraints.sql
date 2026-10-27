-- Ajoute les contraintes absentes dans l'ancienne structure.
-- Les contrôles portent sur les définitions, car init.sql peut avoir créé
-- les mêmes contraintes sous des noms PostgreSQL générés automatiquement.
BEGIN;

DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conrelid = 'dishes'::regclass
      AND contype = 'c'
      AND pg_get_constraintdef(oid) LIKE '%category%burger%tacos%pizza%'
  ) THEN
    ALTER TABLE dishes
      ADD CONSTRAINT dishes_category_allowed_check
      CHECK (category IN ('burger', 'tacos', 'pizza'));
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conrelid = 'dishes'::regclass
      AND contype = 'c'
      AND pg_get_constraintdef(oid) LIKE '%price%>=%0%'
  ) THEN
    ALTER TABLE dishes
      ADD CONSTRAINT dishes_price_nonnegative_check
      CHECK (price >= 0);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conrelid = 'ratings'::regclass
      AND contype = 'c'
      AND pg_get_constraintdef(oid) LIKE '%status%pending%published%rejected%'
  ) THEN
    ALTER TABLE ratings
      ADD CONSTRAINT ratings_status_allowed_check
      CHECK (status IN ('pending', 'published', 'rejected'));
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM pg_constraint
    WHERE conrelid = 'votes'::regclass
      AND contype = 'u'
      AND pg_get_constraintdef(oid) LIKE '%(dish_id, visitor_key)%'
  ) THEN
    ALTER TABLE votes
      ADD CONSTRAINT votes_dish_visitor_unique
      UNIQUE (dish_id, visitor_key);
  END IF;
END $$;

INSERT INTO schema_migrations (version)
VALUES ('001-add-missing-constraints')
ON CONFLICT (version) DO NOTHING;

COMMIT;

SELECT version, applied_at
FROM schema_migrations
ORDER BY version;
