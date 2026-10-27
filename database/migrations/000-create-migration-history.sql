-- Registre des migrations déjà appliquées sur cette base.
-- À exécuter une seule fois avant les migrations numérotées.
CREATE TABLE IF NOT EXISTS schema_migrations (
  version VARCHAR(100) PRIMARY KEY,
  applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);
