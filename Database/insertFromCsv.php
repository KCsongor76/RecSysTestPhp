<?php

$movies_path = "movies.csv";
$links_path = "links.csv";
$ratings_path = "ratings.csv";
$tags_path = "tags.csv";

$movies_query = "INSERT INTO movies (movieId, title, genres) VALUES (?, ?, ?)";
$links_query = "INSERT INTO links (movieId, imdbId, tmdbId) VALUES (?,?, ?)";
$ratings_query = "INSERT INTO ratings (userId, movieId, rating, timestamp) VALUES (?, ?, ?, FROM_UNIXTIME(?))";
$tags_query = "INSERT INTO tags (userId, movieId, tag, timestamp) VALUES (?, ?, ?, FROM_UNIXTIME(?))";

function connectToDb()
{
    // Database credentials
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "movielens_db_2";
    // Create a new connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function insertUsers(): void
{
    $conn = connectToDb();
    $numberstring = "";
    for ($i = 1; $i <= 609; $i++) {
        $numberstring .= "($i), ";
    }
    $numberstring .= "(610)";

    $sql = "INSERT INTO users VALUES $numberstring";
    if ($conn->query($sql)) {
        echo "Users inserted.";
    } else {
        echo $conn->errno;
    }

    $conn->close();
}

function processCSV($csvFile, $query, $type)
{
    // Read the CSV file
    if (($handle = fopen($csvFile, "r")) !== false) {
        // Read the headers
        $headers = fgetcsv($handle);
        $conn = connectToDb();
        // Process each line in the CSV file
        while (($data = fgetcsv($handle)) !== false) {
            // Combine headers with data
            $row = array_combine($headers, $data);

            // Prepare SQL statement
            $sql = $query;
            $stmt = $conn->prepare($sql);

            // Bind parameters and execute the statement
            if ($type == "movies") {
                $stmt->bind_param("iss", $row['movieId'], $row['title'], $row['genres']);
            } else if ($type == "links") {
                $stmt->bind_param("iii", $row['movieId'], $row['imdbId'], $row['tmdbId']);
            } else if ($type == "ratings") {
                $stmt->bind_param("iids", $row['userId'], $row['movieId'], $row['rating'], $row['timestamp']);
            } else if ($type == "tags") {
                $stmt->bind_param("iiss", $row['userId'], $row['movieId'], $row['tag'], $row['timestamp']);
            }
            $stmt->execute();
        }

        fclose($handle);
        echo "Data inserted successfully!";
    } else {
        echo "Error opening the file.";
    }
}

//insertUsers();
//processCSV($movies_path, $movies_query, "movies");
//processCSV($links_path, $links_query, "links");
//processCSV($ratings_path, $ratings_query, "ratings");
//processCSV($tags_path, $tags_query, "tags");