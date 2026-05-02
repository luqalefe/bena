-- gvenzl/oracle-free roda *.sql nesta pasta no primeiro init do banco.
-- Idempotente: se o user já existe (compose subiu, depois caiu, depois subiu),
-- o script segue silenciosamente.
--
-- Em produção este script NÃO é usado — o DBA do tribunal cria o user no
-- Oracle externo. Aqui é só conveniência de dev.

ALTER SESSION SET CONTAINER = FREEPDB1;

DECLARE
  v_count NUMBER;
BEGIN
  SELECT COUNT(*) INTO v_count FROM dba_users WHERE username = 'PONTO';
  IF v_count = 0 THEN
    EXECUTE IMMEDIATE 'CREATE USER ponto IDENTIFIED BY "ponto_dev_only" '
                   || 'DEFAULT TABLESPACE USERS '
                   || 'TEMPORARY TABLESPACE TEMP '
                   || 'QUOTA UNLIMITED ON USERS';
    EXECUTE IMMEDIATE 'GRANT CONNECT, RESOURCE, CREATE SESSION, CREATE TABLE, '
                   || 'CREATE VIEW, CREATE SEQUENCE, CREATE PROCEDURE, '
                   || 'CREATE TRIGGER, CREATE SYNONYM TO ponto';
    DBMS_OUTPUT.PUT_LINE('User PONTO created.');
  ELSE
    DBMS_OUTPUT.PUT_LINE('User PONTO already exists. Skipping.');
  END IF;
END;
/

EXIT;
