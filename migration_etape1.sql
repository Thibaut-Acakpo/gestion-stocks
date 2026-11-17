-- ÉTAPE 1 : à exécuter UNE SEULE FOIS dans phpMyAdmin (base gestion_users, onglet SQL)
-- Vos utilisateurs, produits, catégories et ventes sont conservés.
ALTER TABLE utilisateurs ENGINE = InnoDB;
ALTER TABLE utilisateurs
  ADD COLUMN statut ENUM('actif','inactif') NOT NULL DEFAULT 'actif' AFTER role,
  ADD COLUMN date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD UNIQUE KEY uq_utilisateurs_email (email);
