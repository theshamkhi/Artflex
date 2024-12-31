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



-- Total number of articles published by category

SELECT c.Name AS Category, COUNT(a.ArtID) AS TotalArticles
FROM Categories c
LEFT JOIN Articles a ON c.CatID = a.CatID
WHERE a.status = 'Approved'
GROUP BY c.Name;



-- Most active authors based on the number of articles published

SELECT u.Name AS Author, COUNT(a.ArtID) AS TotalArticles
FROM Users u
JOIN Articles a ON u.UserID = a.AuthorID
WHERE a.status = 'Approved'
GROUP BY u.Name
ORDER BY TotalArticles DESC;



-- Average number of articles published per category

SELECT c.Name AS Category, AVG(ArticleCount) AS AvgArticles
FROM (
    SELECT CatID, COUNT(ArtID) AS ArticleCount
    FROM Articles
    WHERE status = 'Approved'
    GROUP BY CatID
) AS ArticleCountsPerCategory
JOIN Categories c ON ArticleCountsPerCategory.CatID = c.CatID
GROUP BY c.Name;



-- View showing the latest articles published in the last 30 days

CREATE VIEW LatestArticles AS
SELECT a.ArtID, a.Title, a.PubDate, u.Name AS Author, c.Name AS Category
FROM Articles a
JOIN Users u ON a.AuthorID = u.UserID
JOIN Categories c ON a.CatID = c.CatID
WHERE a.status = 'Approved' AND a.PubDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY a.PubDate DESC;


-- Categories without any associated articles

SELECT c.Name AS Category
FROM Categories c
LEFT JOIN Articles a ON c.CatID = a.CatID
WHERE a.CatID IS NULL OR a.status != 'Approved';