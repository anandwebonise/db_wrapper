<?php


/**
 * Wrapper test page
 **/

require("config.php");

// pull in the file with the database class
require("Database.singleton.php");


// create the $db singleton object
$db = Database::obtain(DB_SERVER, DB_USER, DB_PASS, DB_DATABASE);

// connect to the server
$db->connect_pdo();

/**
 * Here first params is table name
 *
 * @params : table name , parameter like conditions and other
 **/


/*echo "<b>#List all organizations</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where()
    ->limit(20)
    ->get();

echo '<pre>';
print_r($getAllOrg);exit;*/


/*echo "<b> List all organisation having id greater than 10</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('id >' => 10))
    ->limit()
    ->get();

echo '<pre>';
print_r($getAllOrg);
exit;*/


/*echo "<b> List all organisation having id greater than 10 and less than 50</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('id >' => 10,'id <=' => 50))
    ->limit()
    ->get();

echo '<pre>';
print_r($getAllOrg);
exit;*/

/*echo "<b> List all organization who has bee created after 2013-02-10 00:00:00</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('created_on >' => '2013-02-10 00:00:00'))
    ->limit()
    ->get();

echo '<pre>';
print_r($getAllOrg);
exit;*/

/*echo "<b> Display informations about organization whose id is 70</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('id' => 70))
    ->limit()
    ->getFirst();

echo '<pre>';
print_r($getAllOrg);
exit;*/

/*echo "<b> Display informations about organization whose name is Org Name 30</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('name' => 'Org Name 30'))
    ->limit()
    ->getFirst();

echo '<pre>';
print_r($getAllOrg);
exit;*/

/*echo "<b> Display informations about organization whose name is Org Name 30</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('name' => 'Org Name 30'))
    ->limit()
    ->getFirst();

echo '<pre>';
print_r($getAllOrg);
exit;*/


/*echo "<b> display all the users of organization_id 30</b>";

$getAllOrg = $db->select(array('organizations.name','COUNT(users.id)'))
    ->from(TABLE_ORGANISATION)
    ->join(array('users'=>'organisation_id','organizations'=>'id'))
    ->group('users.organisation_id')
    ->get();

echo '<pre>';
print_r($getAllOrg);
exit;*/

/*echo "<b> update users table fname = 'abc' and lname = 'xyz' of user whose id is 20</b>";

$updateRecord= $db->update('users',array('fname'=>'abcd','lname'=>'abcd'),array('id'=>20));

if($updateRecord){
   $db->displayError('Record Updated Successfully.');
} else {
    $db->displayError('Failed to update record. Please try again.');
}*/

/*echo "<b>#Delete all users who lives in city City7</b>";
$deleteRecord= $db->delete('users',array('city'=>'City7'));

if($deleteRecord){
    $db->displayError('Record Deleted Successfully.');
} else {
    $db->displayError('Failed to delete record. Please try again.');
}*/


echo "<b> List all organizations who has id between 10 to 50 and its orders should be descending by name</b>";

$getAllOrg = $db->select()
    ->from(TABLE_ORGANISATION)
    ->where(array('id >'=>10,'id <'=>50))
    ->order('name','desc')
    ->get();

echo '<pre>';
print_r($getAllOrg);
exit;