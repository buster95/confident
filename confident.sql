drop database if exists confident;

create database confident;

use confident;

CREATE TABLE usuarios (
    idUsuario INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50),
    apellido VARCHAR(50)
);
Insert Into usuarios (nombre, apellido) Values
('Walter', 'Corrales'),
('Jon', 'Uriel'),
('Kleopatros', 'Peer'),
('Jordon', 'Hallsteinn'),
('Rolando Gerfrid', 'Kaj Keinan'),
('Anuj Neofit', 'Arrats Darin'),
('Euthymios', 'Eanraig'),
('Kristen', 'Ninoslav'),
('Keshawn', 'Georg'),
('Valentín', 'Bento'),
('Tristan', 'Androkles'),
('Sherwood', 'Agathangelos'),
('Amias', 'Ernst'),
('Floris', 'Hallsteinn'),
('Siemowit', 'Lir'),
('Kartik', 'Olufunmilayo'),
('Eardwulf', 'Egil'),
('René', 'Doru'),
('Dip', 'Giambattista'),
('Gero', 'Onyekachukwu'),
('Valeriu', 'Cnut'),
('Wilmaer', 'Suresh'),
('Hendrik', 'Masud'),
('Klaudio', 'Robert'),
('Muscowequan', 'Vladislav'),
('Herbie', 'Dubaku'),
('Iuppiter', 'Eizens'),
('Wetzel', 'Clinton'),
('Rishi', 'Husam'),
('Shelby', 'Shay'),
('Walker', 'Helgi'),
('Dionysos', 'Sebastiaan'),
('Spartak', 'Raimondas'),
('Nolan', 'Heraclius'),
('Elbert', 'Fedelmid'),
('Fridrihs', 'Gedaliah'),
('Tito', 'Milan');