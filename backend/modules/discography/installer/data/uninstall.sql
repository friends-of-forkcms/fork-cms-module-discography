-- Drop module tables
DROP TABLE IF EXISTS discography_albums;
DROP TABLE IF EXISTS discography_albums_tracks;
DROP TABLE IF EXISTS discography_categories;

-- Remove from backend navigation
DELETE FROM backend_navigation WHERE label = 'discography';
DELETE FROM backend_navigation WHERE url = '%discography%';

-- Remove from groups_rights
DELETE FROM groups_rights_actions WHERE module = 'discography';
DELETE FROM groups_rights_modules WHERE module = 'discography';

-- Remove from locale
DELETE FROM locale WHERE module = 'discography';
DELETE FROM locale WHERE module = 'core' AND name = 'discography%';

-- Remove from modules
DELETE FROM modules WHERE name = 'discography';
DELETE FROM modules_extras WHERE module = 'discography';

-- Remove from Meta
DELETE FROM meta WHERE keywords = '%discography%';