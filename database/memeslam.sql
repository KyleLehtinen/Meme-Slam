-- drop/recreate database
drop database if exists MemeSlamDev;
create database MemeSlamDev;
use MemeSlamDev;

-- Create MogMaster table
create table MogMaster(
	id int AUTO_INCREMENT PRIMARY KEY,
	name varchar(255) NOT NULL,
	img_url varchar(255) NOT NULL,
	src_url varchar(255) NOT NULL,
	rating int,
	active boolean default 1,
	updated_at datetime on update current_timestamp,
	created_at datetime default current_timestamp
);

-- Create Instantiated Mog List table
create table ActivatedMogs(
	id int AUTO_INCREMENT PRIMARY KEY,
	mog_ID int NOT NULL,
	exchanges int NOT NULL,
	recent boolean,
	updated_at datetime on update current_timestamp,
	created_at datetime default current_timestamp
);

-- Create User Table
create table User(
	id int AUTO_INCREMENT PRIMARY KEY,
	name varchar(255) NOT NULL,
	password varchar(255) NOT NULL,
	email varchar(255) NOT NULL,
	collection_rating int DEFAULT 0,
	casual_wins int DEFAULT 0,
	keeps_wins int DEFAULT 0,
	total_wins int DEFAULT 0,
	game_count int DEFAULT 0,
	last_match datetime,
	bet_pod_rating numeric(6,2),
	remember_token varchar(255),
	updated_at datetime on update current_timestamp,
	created_at datetime default current_timestamp,
	last_login datetime on update current_timestamp
);

-- Create Master Mog/User join Table
create table UserMogs(
	mog_ID int NOT NULL,
	user_ID int NOT NULL,
	on_bet boolean NOT NULL,
	updated_at datetime on update current_timestamp,
	created_at datetime default current_timestamp
);

-- Create Match table
create table Matches(
	id int AUTO_INCREMENT PRIMARY KEY,
	p1_ID int NOT NULL,
	p1_accept boolean DEFAULT FALSE,
	p2_ID int NOT NULL,
	p2_accept boolean DEFAULT FALSE,
	p1_turn boolean,
	in_progress boolean DEFAULT TRUE,
	p1_new_mogs int,
	p2_new_mogs int,
	updated_at datetime on update current_timestamp,
	created_at datetime default current_timestamp
);

-- Create PlayField join table
create table PlayField(
	match_ID int NOT NULL,
	mog_ID int NOT NULL,
	flipped boolean default false,
	updated_at datetime on update current_timestamp,
	created_at datetime default current_timestamp
);
