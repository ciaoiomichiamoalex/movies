-- SCHEMA movies
DROP SCHEMA IF EXISTS movies;
CREATE SCHEMA IF NOT EXISTS movies DEFAULT CHARACTER SET UTF8;
USE movies;

-- TABLE users
DROP TABLE IF EXISTS users;
CREATE TABLE IF NOT EXISTS users (
    codice INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    username VARCHAR(20) NOT NULL,
    password VARCHAR(40) NOT NULL,
    creazione VARCHAR(20) NOT NULL,
    modifica VARCHAR(20) NULL,
    eliminazione VARCHAR(20) NULL,
    bloccato TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY (codice),
    UNIQUE (email, username))
ENGINE = InnoDB;

-- TABLE movies
DROP TABLE IF EXISTS movies;
CREATE TABLE IF NOT EXISTS movies (
    codice INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL,
    recensione INT NOT NULL,
    creazione VARCHAR(20) NOT NULL,
    modifica VARCHAR(20) NULL,
    eliminazione VARCHAR(20) NULL,
    ordine INT NOT NULL,
    user INT NOT NULL,
    visibile TINYINT NOT NULL DEFAULT 1,
    PRIMARY KEY (codice),
    CONSTRAINT fk_movies_users
        FOREIGN KEY (user)
        REFERENCES users (codice)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT)
ENGINE = InnoDB;

-- USER manager
DROP USER IF EXISTS manager;
CREATE USER 'manager' IDENTIFIED BY 'manager';
GRANT ALL ON movies.* TO 'manager';

-- USER nameless
DROP USER IF EXISTS nameless;
CREATE USER 'nameless' IDENTIFIED BY 'nameless';
GRANT SELECT, INSERT, UPDATE ON TABLE movies.* TO 'nameless';
