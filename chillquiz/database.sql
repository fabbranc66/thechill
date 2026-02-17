CREATE TABLE admin (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50),
password VARCHAR(255)
);
INSERT INTO admin (username, password)
VALUES ('fabbranc', MD5('GenniH264rgnm'));
CREATE TABLE quiz (
id INT AUTO_INCREMENT PRIMARY KEY,
titolo VARCHAR(200),
livello INT DEFAULT 1,
tempo_domanda INT DEFAULT 15
);
INSERT INTO quiz (titolo, livello, tempo_domanda)
VALUES ('Musica',1,15);
CREATE TABLE domande (
id INT AUTO_INCREMENT PRIMARY KEY,
quiz_id INT,
testo TEXT
);
CREATE TABLE risposte (
id INT AUTO_INCREMENT PRIMARY KEY,
domanda_id INT,
testo TEXT,
corretta TINYINT(1)
);
CREATE TABLE partite (
id INT AUTO_INCREMENT PRIMARY KEY,
quiz_id INT,
pin VARCHAR(6)
);
CREATE TABLE giocatori (
id INT AUTO_INCREMENT PRIMARY KEY,
partita_id INT,
nome VARCHAR(100),
punteggio INT DEFAULT 0
);