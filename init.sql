-- init.sql : structure de la base
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);

CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    quantity INTEGER NOT NULL
);

CREATE TABLE history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user TEXT NOT NULL,
    book_id INTEGER NOT NULL,
    action TEXT CHECK(action IN ('emprunt', 'retour', 'ajout')),
    date TEXT NOT NULL
);


INSERT INTO books (title, quantity) VALUES
('Algorithme des Graphes', 5),
('Atelier de programation', 4),
('C vs C++', 2),
('Complexité', 3),
('Cyber-sécurité M2', 2),
('Finance pour les nuls', 5),
('Java pour débutant', 3),
('Git', 4);




INSERT INTO users (login, password) VALUES ('achraf', 'achraf1996');
INSERT INTO users (login, password) VALUES ('reda', 'reda2003');
