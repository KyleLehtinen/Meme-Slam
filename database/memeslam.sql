-- drop/recreate database
drop database if exists MemeSlamDev;
create database MemeSlamDev;
use MemeSlamDev;

-- Create MogMaster table
create table MogMaster(
	id int AUTO_INCREMENT PRIMARY KEY,
	name varchar(255) NOT NULL,
	imgUrl varchar(255) NOT NULL,
	srcUrl varchar(255) NOT NULL,
	srcFaves int NOT NULL,
	srcViews int NOT NULL,
	srcOrigin varchar(255) NOT NULL,
	rating int,
	updated_at datetime,
	created_at datetime
);

-- Create Instantiated Mog List table
create table ActivatedMogs(
	id int AUTO_INCREMENT PRIMARY KEY,
	mogID int NOT NULL,
	exchanges int NOT NULL,
	recent boolean,
	updated_at datetime,
	created_at datetime
);

-- Create User Table
create table User(
	id int AUTO_INCREMENT PRIMARY KEY,
	name varchar(255) NOT NULL,
	password varchar(255) NOT NULL,
	email varchar(255) NOT NULL,
	collectionRating int DEFAULT 0,
	casualWins int DEFAULT 0,
	keepsWins int DEFAULT 0,
	totalWins int DEFAULT 0,
	gameCount int DEFAULT 0,
	lastMatch datetime,
	betPodRating numeric(6,2),
	remember_token varchar(255),
	updated_at datetime,
	created_at datetime
);

-- Create Master Mog/User join Table
create table UserMogs(
	mogID int NOT NULL,
	userID int NOT NULL,
	onBet boolean NOT NULL,
	updated_at datetime,
	created_at datetime
);

-- Create Match table
create table Matches(
	id int AUTO_INCREMENT PRIMARY KEY,
	p1ID int NOT NULL,
	p1AcceptMatch boolean DEFAULT FALSE,
	p2ID int NOT NULL,
	p2AcceptMatch boolean DEFAULT FALSE,
	p1Turn boolean,
	inProgress boolean DEFAULT TRUE,
	p1NewMogs int,
	p2NewMogs int,
	updated_at datetime,
	created_at datetime
);

-- Create PlayField join table
create table PlayField(
	matchID int NOT NULL,
	mogID int NOT NULL,
	flipped boolean default false,
	updated_at datetime,
	created_at datetime
);
