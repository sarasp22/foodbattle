-- Crée users sur les bases qui ne possèdent pas encore cette table.
-- Sur la base locale où elle a été créée manuellement, aucun doublon n'est créé.
BEGIN;

CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  nickname VARCHAR(60) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'user'
    CHECK (role IN ('user', 'admin')),
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO schema_migrations (version)
VALUES ('002-create-users-table')
ON CONFLICT (version) DO NOTHING;

COMMIT;
