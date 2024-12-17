-- Create News table
CREATE TABLE IF NOT EXISTS News (
    newsId INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES Users(userId)
);

-- Create NewsLikes table
CREATE TABLE IF NOT EXISTS NewsLikes (
    likeId INT PRIMARY KEY AUTO_INCREMENT,
    newsId INT NOT NULL,
    userId INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (newsId) REFERENCES News(newsId),
    FOREIGN KEY (userId) REFERENCES Users(userId),
    UNIQUE KEY unique_news_like (newsId, userId)
);

-- Create NewsComments table
CREATE TABLE IF NOT EXISTS NewsComments (
    commentId INT PRIMARY KEY AUTO_INCREMENT,
    newsId INT NOT NULL,
    userId INT NOT NULL,
    commentText TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (newsId) REFERENCES News(newsId),
    FOREIGN KEY (userId) REFERENCES Users(userId)
);
