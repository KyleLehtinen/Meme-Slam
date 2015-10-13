-- drop/recreate database
drop database if exists MemeSlam;
create database MemeSlam;
use MemeSlam;

-- Create MogMaster table
create table MogMaster(
	id int AUTO_INCREMENT PRIMARY KEY,
	name varchar(255) NOT NULL,
	imgUrl varchar(255) NOT NULL,
	localPath varchar(255) NOT NULL,
	srcUrl varchar(255) NOT NULL,
	srcFaves int NOT NULL,
	srcViews int NOT NULL,
	srcOrigin varchar(255) NOT NULL,
	rating int,
	rateBias int
);

-- Create Instantiated Mog List table
create table ActivatedMogs(
	id int AUTO_INCREMENT PRIMARY KEY,
	mogID int NOT NULL,
	userID int NOT NULL,
	invID int NOT NULL,
	exchanges int NOT NULL
);

-- Create User Table
create table User(
	id int AUTO_INCREMENT PRIMARY KEY,
	username varchar(255) NOT NULL,
	password varchar(255) NOT NULL,
	email varchar(255) NOT NULL,
	collectionRating int NOT NULL,
	casualWins int NOT NULL,
	keepsWins int NOT NULL,
	totalWins int NOT NULL,
	gameCount int NOT NULL
);

-- Create Master Inventory List Table
create table InventoryMaster(
	invID int NOT NULL,
	userID int NOT NULL
);

