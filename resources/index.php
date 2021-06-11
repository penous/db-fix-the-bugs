<?php
declare(strict_types=1);

$sports = ['Football', 'Tennis', 'Ping pong', 'Volley ball', 'Rugby', 'Horse riding', 'Swimming', 'Judo', 'Karate'];

function openConnection(): PDO
{
    // No bugs in this function, just use the right credentials.
    $dbhost = "localhost";
    $dbuser = "becode";
    $dbpass = "becode";
    $db = "test";

    $driverOptions = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    return new PDO('mysql:host=' . $dbhost . ';dbname=' . $db, $dbuser, $dbpass, $driverOptions);
}

$pdo = openConnection();

if (!empty($_POST['firstname']) && !empty($_POST['lastname'])) {
    //@todo possible bug below? Done
    var_dump($_POST);
    if (empty($_POST['id'])) {
        $handle = $pdo->prepare('INSERT INTO user (firstname, lastname, year) VALUES (:firstname, :lastname, :year)');
        $message = 'Your record has been added';
    } else {
        //@todo why does this not work? Done
        $handle = $pdo->prepare('UPDATE user SET firstname = :firstname, lastname = :lastname, year = :year WHERE id = :id');
        $handle->bindValue(':id', $_POST['id']);
        $message = 'Your record has been updated';
    }

    $handle->bindValue(':firstname', $_POST['firstname']);
    $handle->bindValue(':lastname', $_POST['lastname']);
    $handle->bindValue(':year', date('Y'));
    $handle->execute();

    if (!empty($_POST['id'])) {
        $handle = $pdo->prepare('DELETE FROM sport WHERE user_id = :id');
        $handle->bindValue(':id', $_POST['id']);
        $handle->execute();
        $userId = $_POST['id'];
    } else {
        //why did I leave this if empty? There must be no important reason for this. Move on. Done
        $userId = $pdo->lastInsertId(); //- In the loop it takes the sport ID since it was the last inserted!
    }

    //@todo Why does this loop not work? If only I could see the bigger picture. Done
    foreach ($_POST['sports'] as $sport) {
        $handle = $pdo->prepare('INSERT INTO sport (user_id, sport) VALUES (:userId, :sport)');
        $handle->bindValue(':userId', $userId);
        $handle->bindValue(':sport', $sport);
        $handle->execute();
    }
} elseif (isset($_POST['delete'])) {
    //@todo BUG? Why does always delete all my users? Done
    $handle = $pdo->prepare('DELETE FROM user where id = :id');
    //The line below just gave me an error, probably not important. Annoying line. Done
    $handle->bindValue(':id', $_POST['id']);
    $handle->execute();

    $message = 'Your record has been deleted';
}

//@todo Invalid query? Done
$handle = $pdo->prepare('SELECT user.id, concat_ws(firstname, lastname, " ") AS name, sport FROM user LEFT JOIN sport ON user.id = sport.user_id where year = :year order by sport');
$handle->bindValue(':year', date('Y'));
$handle->execute();
$users = $handle->fetchAll();

$saveLabel = 'Save record';
if (!empty($_GET['id'])) {
    $saveLabel = 'Update record';

    $handle = $pdo->prepare('SELECT id, firstname, lastname FROM user where id = :id');
    $handle->bindValue(':id', $_GET['id']);
    $handle->execute();
    $selectedUser = $handle->fetch();
    var_dump($selectedUser);

    //This segment checks all the current sports for an existing user when you update him. Currently that is not working however. :-(
    $selectedUser['sports'] = [];
    $handle = $pdo->prepare('SELECT sport FROM sport where user_id = :id');
    $handle->bindValue(':id', $_GET['id']);
    $handle->execute();
    $sportss = $handle->fetchAll();
    foreach ($sportss as $sport) {
        var_dump($sport);
        // $selectedUser['sports'][] = $sport;//@todo I just want an array of all sports of this, why is it not working?
        array_push($selectedUser['sports'], $sport['sport']);
    }
    var_dump($selectedUser);
}

if (empty($selectedUser['id'])) {
    $selectedUser = [
        'id' => '',
        'firstname' => '',
        'lastname' => '',
        'sports' => []
    ];
}

require 'view.php';
// All bugs where written with Love for the learning Process. No actual bugs where harmed or eaten during the creation of this code.