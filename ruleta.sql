SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `ruleta` DEFAULT CHARACTER SET latin1 ;

-- -----------------------------------------------------
-- Table `ruleta`.`games`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ruleta`.`games` ;

CREATE  TABLE IF NOT EXISTS `ruleta`.`games` (
  `idgames` INT(11) NOT NULL AUTO_INCREMENT ,
  `type` VARCHAR(45) NULL DEFAULT NULL ,
  `timestamp` DATETIME NOT NULL ,
  PRIMARY KEY (`idgames`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `ruleta`.`globals`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ruleta`.`globals` ;

CREATE  TABLE IF NOT EXISTS `ruleta`.`globals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `pass` VARCHAR(45) NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `ruleta`.`persons`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ruleta`.`persons` ;

CREATE  TABLE IF NOT EXISTS `ruleta`.`persons` (
  `idpersons` INT(11) NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`idpersons`) )
ENGINE = InnoDB
AUTO_INCREMENT = 5
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `ruleta`.`nicks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ruleta`.`nicks` ;

CREATE  TABLE IF NOT EXISTS `ruleta`.`nicks` (
  `idpersons` INT(11) NULL ,
  `name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`name`) ,
  INDEX `fk_table2_persons` (`idpersons` ASC) ,
  CONSTRAINT `fk_table2_persons`
    FOREIGN KEY (`idpersons` )
    REFERENCES `ruleta`.`persons` (`idpersons` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `ruleta`.`matches`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ruleta`.`matches` ;

CREATE  TABLE IF NOT EXISTS `ruleta`.`matches` (
  `game` INT(11) NOT NULL ,
  `nick` VARCHAR(45) NOT NULL ,
  `team` INT(11) NOT NULL ,
  `victory` VARCHAR(1) NULL DEFAULT 'N' ,
  PRIMARY KEY (`nick`, `game`) ,
  INDEX `fk_nicks_has_games_nicks1` (`nick` ASC) ,
  INDEX `fk_nicks_has_games_games1` (`game` ASC) ,
  CONSTRAINT `fk_nicks_has_games_nicks1`
    FOREIGN KEY (`nick` )
    REFERENCES `ruleta`.`nicks` (`name` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_nicks_has_games_games1`
    FOREIGN KEY (`game` )
    REFERENCES `ruleta`.`games` (`idgames` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Placeholder table for view `ruleta`.`matches_stats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruleta`.`matches_stats` (`name` INT, `team` INT, `victory` INT, `type` INT, `timestamp` INT);

-- -----------------------------------------------------
-- Placeholder table for view `ruleta`.`nicks_played`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruleta`.`nicks_played` (`name` INT, `played` INT);

-- -----------------------------------------------------
-- Placeholder table for view `ruleta`.`nicks_won`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruleta`.`nicks_won` (`name` INT, `won` INT);

-- -----------------------------------------------------
-- Placeholder table for view `ruleta`.`person_performance`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruleta`.`person_performance` (`person` INT, `nick` INT, `team` INT, `victory` INT, `timestamp` INT);

-- -----------------------------------------------------
-- Placeholder table for view `ruleta`.`nicks_lost`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruleta`.`nicks_lost` (`name` INT, `lost` INT);

-- -----------------------------------------------------
-- Placeholder table for view `ruleta`.`nicks_stats`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruleta`.`nicks_stats` (`name` INT, `played` INT, `won` INT, `lost` INT);

-- -----------------------------------------------------
-- View `ruleta`.`matches_stats`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `ruleta`.`matches_stats` ;
DROP TABLE IF EXISTS `ruleta`.`matches_stats`;
CREATE  OR REPLACE VIEW `ruleta`.`matches_stats` AS
SELECT `n`.`name` AS `name`,`m`.`team` AS `team`,`m`.`victory` AS `victory`,`g`.`type` AS `type`,`g`.`timestamp` AS `timestamp`
FROM ((`ruleta`.`nicks` `n` JOIN `ruleta`.`matches` `m` ON ((`n`.`name` = `m`.`nick`)))
JOIN `ruleta`.`games` `g` ON ((`m`.`game` = `g`.`idgames`))) 
ORDER BY `g`.`timestamp` DESC,`m`.`victory` DESC;

-- -----------------------------------------------------
-- View `ruleta`.`nicks_played`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `ruleta`.`nicks_played` ;
DROP TABLE IF EXISTS `ruleta`.`nicks_played`;
CREATE  OR REPLACE VIEW `ruleta`.`nicks_played` AS
SELECT `matches_stats`.`name` AS `name`, count(`matches_stats`.`name`) AS `played` FROM `ruleta`.`matches_stats` GROUP BY `matches_stats`.`name`;

-- -----------------------------------------------------
-- View `ruleta`.`nicks_won`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `ruleta`.`nicks_won` ;
DROP TABLE IF EXISTS `ruleta`.`nicks_won`;
CREATE  OR REPLACE VIEW `ruleta`.`nicks_won` AS 
SELECT `matches_stats`.`name` AS `name`, count(`matches_stats`.`victory`) AS `won` 
FROM `ruleta`.`matches_stats` WHERE (`matches_stats`.`victory` = 'Y') GROUP BY `matches_stats`.`name`;

-- -----------------------------------------------------
-- View `ruleta`.`person_performance`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `ruleta`.`person_performance` ;
DROP TABLE IF EXISTS `ruleta`.`person_performance`;
CREATE  OR REPLACE VIEW `ruleta`.`person_performance` AS
SELECT `p`.`name` AS `person`,`n`.`name` AS `nick`,`m`.`team` AS `team`,`m`.`victory` AS `victory`,`g`.`timestamp` AS `timestamp`
FROM (((`ruleta`.`persons` `p` LEFT JOIN `ruleta`.`nicks` `n` ON ((`n`.`idpersons` = `p`.`idpersons`))) 
LEFT JOIN `ruleta`.`matches` `m` ON ((`m`.`nick` = `n`.`name`)))
LEFT JOIN `ruleta`.`games` `g` ON ((`m`.`game` = `g`.`idgames`)));

-- -----------------------------------------------------
-- View `ruleta`.`nicks_lost`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `ruleta`.`nicks_lost` ;
DROP TABLE IF EXISTS `ruleta`.`nicks_lost`;
CREATE  OR REPLACE VIEW `ruleta`.`nicks_lost` AS
SELECT `matches_stats`.`name` AS `name`, count(`matches_stats`.`victory`) AS `lost` 
FROM `ruleta`.`matches_stats` WHERE (`matches_stats`.`victory` = 'N') GROUP BY `matches_stats`.`name`
;

-- -----------------------------------------------------
-- View `ruleta`.`nicks_stats`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `ruleta`.`nicks_stats` ;
DROP TABLE IF EXISTS `ruleta`.`nicks_stats`;
CREATE  OR REPLACE VIEW `ruleta`.`nicks_stats` AS
SELECT n.name, IFNULL(np.played,0) AS played, IFNULL(nw.won,0) AS won, IFNULL(nl.lost,0) AS lost
FROM nicks AS n
LEFT JOIN
nicks_played AS np ON n.name=np.name
LEFT JOIN
nicks_won AS nw ON n.name=nw.name
LEFT JOIN
nicks_lost AS nl ON n.name=nl.name
;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `ruleta`.`globals`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO `ruleta`.`globals` (`id`, `pass`) VALUES (1, 'mylifeforaiur');

COMMIT;

-- -----------------------------------------------------
-- Data for table `ruleta`.`persons`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO `ruleta`.`persons` (`idpersons`, `name`) VALUES (1, 'Fede');
INSERT INTO `ruleta`.`persons` (`idpersons`, `name`) VALUES (2, 'Geri');
INSERT INTO `ruleta`.`persons` (`idpersons`, `name`) VALUES (3, 'Aceve');
INSERT INTO `ruleta`.`persons` (`idpersons`, `name`) VALUES (4, 'Alfred');

COMMIT;

-- -----------------------------------------------------
-- Data for table `ruleta`.`nicks`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (1, 'Dread');
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (2, 'TylerDurden');
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (3, 'MERLINux');
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (3, 'BeMySluts');
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (4, 'Judge');
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (5, 'DarthHoracio');
INSERT INTO `ruleta`.`nicks` (`idpersons`, `name`) VALUES (6, 'Shrike');

COMMIT;
