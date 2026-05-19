ALTER TABLE users
    MODIFY token_user CHAR(64) NOT NULL;

ALTER TABLE accounts
    MODIFY token CHAR(64) NOT NULL;

UPDATE users
SET token_user = SHA2(token_user, 256)
WHERE CHAR_LENGTH(token_user) <> 64
   OR token_user NOT REGEXP '^[A-Fa-f0-9]{64}$';

UPDATE accounts
SET token = SHA2(token, 256)
WHERE CHAR_LENGTH(token) <> 64
   OR token NOT REGEXP '^[A-Fa-f0-9]{64}$';
