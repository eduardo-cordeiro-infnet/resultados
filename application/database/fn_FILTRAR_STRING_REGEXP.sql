DROP FUNCTION IF EXISTS `FILTRAR_STRING_REGEXP`;
DELIMITER //
/* http://stackoverflow.com/a/22903586/1815558 */
CREATE FUNCTION `FILTRAR_STRING_REGEXP`(str VARCHAR(1000), regexp_str VARCHAR(50)) RETURNS varchar(1000) CHARSET utf8
BEGIN
  DECLARE i, len SMALLINT DEFAULT 1;
  DECLARE ret VARCHAR(1000) DEFAULT '';
  DECLARE c CHAR(1);

  IF COALESCE(regexp_str, '') = '' THEN
    SET regexp_str = '[[:alnum:]]';
  END IF;

  SET len = CHAR_LENGTH( str );
  REPEAT
    BEGIN
      SET c = MID( str, i, 1 );
      IF c REGEXP regexp_str THEN
        SET ret=CONCAT(ret,c);
      END IF;
      SET i = i + 1;
    END;
  UNTIL i > len END REPEAT;
  RETURN ret;
END//
DELIMITER ;
