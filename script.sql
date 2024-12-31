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


-- Insert example data into Users
INSERT INTO Users (Name, Username, Password, Role)
VALUES
('Alice Johnson', 'alicej', '123', 'Admin'),
('Bob Smith', 'bobsmith', '456', 'Author'),
('Charlie Brown', 'charlieb', '789', 'Reader');


-- Insert example data into Categories
INSERT INTO Categories (Name)
VALUES
('Technology'),
('Health'),
('Travel'),
('Education');


-- Insert example data into Articles
INSERT INTO Articles (AuthorID, CatID, PhotoURL, Title, Content, PubDate, status)
VALUES
(2, 1, '', 'The Future of AI', 'Content about AI...', NOW(), 'Approved'),
(2, 3, '', 'Top 10 Travel Destinations', 'Content about travel...', NOW(), 'Pending'),
(2, 4, '', 'Benefits of Online Learning', 'Content about education...', NOW(), 'Rejected');