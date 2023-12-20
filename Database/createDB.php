<?php

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "movielens_db_2";

//// Create a new connection
//$conn = new mysqli($servername, $username, $password);
//
//// Check the connection
//if ($conn->connect_error) {
//    die("Connection failed: " . $conn->connect_error);
//}
//
//// Create the database
//if ($conn->query("CREATE DATABASE $dbname") === TRUE) {
//    echo "Database created successfully";
//} else {
//    echo "Error creating database: " . $conn->error;
//}
//
//mysqli_close($conn);

// Create a new connection to the created database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL queries to create the tables
$sql = "CREATE TABLE users (
    userId INT(11) PRIMARY KEY
);";

$sql .= "CREATE TABLE movies (
    movieId INT(11) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genres VARCHAR(255) NOT NULL
);";

$sql .= "CREATE TABLE links (
    movieId INT(11),
    imdbId INT(11),
    tmdbId INT(11),
    PRIMARY KEY (movieId),
    FOREIGN KEY (movieId) REFERENCES movies(movieId)
);";

$sql .= "CREATE TABLE ratings (
    userId INT(11),
    movieId INT(11),
    rating DECIMAL(2,1) NOT NULL,
    timestamp TIMESTAMP NOT NULL,
    PRIMARY KEY (userId, movieId),
    FOREIGN KEY (userId) REFERENCES users(userId),
    FOREIGN KEY (movieId) REFERENCES movies(movieId)
);";

$sql .= "CREATE TABLE tags (
    userId INT(11),
    movieId INT(11),
    tag VARCHAR(255) NOT NULL,
    timestamp TIMESTAMP NOT NULL,
    PRIMARY KEY (userId, movieId, tag),
    FOREIGN KEY (userId) REFERENCES users(userId),
    FOREIGN KEY (movieId) REFERENCES movies(movieId)
);";

// Execute the SQL queries
if ($conn->multi_query($sql) === TRUE) {
    echo "Tables created successfully";
} else {
    echo "Error creating tables: " . $conn->error;
}

mysqli_close($conn);

