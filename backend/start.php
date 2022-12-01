<?php
require 'vendor/autoload.php';

use Src\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$dbConnection = (new Database())->connet();
$db = $_ENV['DB_DATABASE'];

// https://programadoresdepre.com.br/sistema-de-login-com-php-e-mysql-pdo/
// https://www.youtube.com/watch?v=Wv02i0yNVVs
// https://www.botecodigital.dev.br/php/jwt-json-web-token-em-php/
// https://imasters.com.br/back-end/entendendo-o-jwt
// achei isso em 40 min... vamos de né. pelo menos nao fiquei a toa haha resto foi jogando e pá

$query = "CREATE TABLE IF NOT EXISTS `$db`.`tags` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `importante` TINYINT NULL DEFAULT 0,
    `urgente` TINYINT NULL DEFAULT 0,
    `favorito` TINYINT NULL DEFAULT 0,
    `data_criacao` DATETIME NOT NULL,
    `data_edicao` DATETIME NULL,
    `ativo` VARCHAR(45) NOT NULL DEFAULT 'A',
    PRIMARY KEY (`id`))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_bin;

  CREATE TABLE IF NOT EXISTS `$db`.`imoveis` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(3000) NOT NULL,
    `data_cadastro` DATETIME NOT NULL,
    `descricao` VARCHAR(5000) NOT NULL,
    `preco` FLOAT NOT NULL,
    `cep` VARCHAR(15) NOT NULL,
    `rua` VARCHAR(100) NOT NULL,
    `bairro` VARCHAR(100) NOT NULL,
    `numero` VARCHAR(100) NULL,
    `cidade` VARCHAR(100) NOT NULL,
    `estado` VARCHAR(100) NOT NULL,
    `complemento` VARCHAR(45) NULL,
    `tags_id` INT NOT NULL,
    `data_edicao` DATETIME NULL,
    `ativo` VARCHAR(45) NOT NULL DEFAULT 'A',
    PRIMARY KEY (`id`),
    INDEX `fk_imoveis_tags1_idx` (`tags_id` ASC) VISIBLE,
    CONSTRAINT `fk_imoveis_tags1`
      FOREIGN KEY (`tags_id`)
      REFERENCES `$db`.`tags` (`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_bin;

  CREATE TABLE IF NOT EXISTS `$db`.`arquivos` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(500) NOT NULL,
    `data_upload` DATETIME NOT NULL,
    `caminho` VARCHAR(5000) NOT NULL,
    `nome_salvo` VARCHAR(500) NOT NULL,
    `imovel_id` INT NULL,
    `data_edicao` DATETIME NULL,
    `ativo` VARCHAR(45) NOT NULL DEFAULT 'A',
    `nome_original` VARCHAR(500) NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_arquivo_imovel_idx` (`imovel_id` ASC) VISIBLE,
    CONSTRAINT `fk_arquivo_imovel`
      FOREIGN KEY (`imovel_id`)
      REFERENCES `$db`.`imoveis` (`id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_bin;

  CREATE TABLE IF NOT EXISTS `imobibrasil`.`users` (
    `email` VARCHAR(500) NOT NULL,
    `password` VARCHAR(100) NOT NULL,
    `data_edicao` DATETIME NULL,
    `data_criacao` DATETIME NOT NULL,
    `type` VARCHAR(45) NOT NULL DEFAULT 'user',
    PRIMARY KEY (`email`))
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_bin;";

try {
  $dbConnection->exec($query);
} catch (\PDOException $e) {
  exit($e->getMessage());
}
