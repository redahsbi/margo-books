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
    action TEXT CHECK(action IN ('emprunt', 'retour')),
    date TEXT NOT NULL
);

INSERT INTO books (title, quantity) VALUES
('Java pour débutant', 3),
('Algorithme des Graphes', 5),
('Cyber-sécurité M2', 2),
('Git', 4);
('Complexité', 3),
('Finance pour les nuls', 5),
('C vs C++', 2),
('Atelier de programation', 4);

INSERT INTO users (login, password) VALUES ('admin', 'admin');
