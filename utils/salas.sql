--
-- Create schema reservaSalas
--

CREATE DATABASE IF NOT EXISTS reservaSalas;
USE reservaSalas;

--
-- Definition of table `alumnos`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario` varchar(45) NOT NULL,
  `departamento` varchar(45) NOT NULL,
  `contrasena` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `usuarios`
--

/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` (`id`,`usuario`,`departamento`,`contrasena`) VALUES 
 (1,'miUsuario','ventas','123'),
 (2,'nombreUsuario','marketing','1234');


--
-- Definition of table `salas`
--

DROP TABLE IF EXISTS `salas`;
CREATE TABLE `salas` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sala` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `salas` (`id`,`sala`) VALUES 
 (1,'Sala pequeña'),
 (2,'Sala grande'),
 (3,'Sala pequeña');


DROP TABLE IF EXISTS `material`;
CREATE TABLE `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `material` (`id`,`item`) VALUES 
  (1,'Proyector'),
  (2,'Catering'),
  (3,'Sillas adicionales'),
  (4,'Equipo videoconferencia');





DROP TABLE IF EXISTS `reservas`;
CREATE TABLE `reservas` (
    `idSala` int(10) NOT NULL,
    `idUsuario` int(10) NOT NULL,
    `inicio` DATETIME NOT NULL,
    `material` varchar(80),
    `comentarios` varchar(200),
  PRIMARY KEY (`idSala`,`inicio`)

)ENGINE=InnoDB DEFAULT CHARSET=latin1;
