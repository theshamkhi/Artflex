CREATE DATABASE Artflex;
USE Artflex;

CREATE TABLE Users (
    UserID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255) NOT NULL,
    Username VARCHAR(100),
    Password VARCHAR(100),
    Role ENUM('Admin', 'Author', 'Reader')
);

CREATE TABLE Categories (
    CatID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL
);

CREATE TABLE Articles (
    ArtID INT AUTO_INCREMENT PRIMARY KEY,
    AuthorID INT NOT NULL,
    CatID INT NOT NULL,
    PhotoURL VARCHAR(255),
    Title VARCHAR(255) NOT NULL,
    Content TEXT,
    PubDate DATETIME NOT NULL,
    status ENUM('Approved', 'Rejected', 'Pending') DEFAULT 'Pending',
    FOREIGN KEY (AuthorID) REFERENCES Users(UserID),
    FOREIGN KEY (CatID) REFERENCES Categories(CatID)
);


