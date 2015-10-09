<?php
$serverName = "127.0.0.1\\SQLEXPRESS";
$connectionInfo = array("Database"=>"PNU", "UID"=>"user1", "PWD"=>"kopex");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if( $conn ) {
     echo "Connection established.<br />";
}else{
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}
// phpinfo();

exit;
// exit;

//connection to the database
$dbhandle = mssql_connect($myServer, $myUser, $myPass)
  or die("Couldn't connect to SQL Server on $myServer"); 

//select a database to work with
$selected = mssql_select_db($myDB, $dbhandle)
  or die("Couldn't open database $myDB"); 

//declare the SQL statement that will query the database
// $query = "SELECT id, name, year ";
// $query .= "FROM cars ";
// $query .= "WHERE name='BMW'"; 
$query = "SELECT * FROM [PNU].[dbo].[rbh] WHERE 1;"; 

//execute the SQL query and return records
$result = mssql_query($query);

$numRows = mssql_num_rows($result); 
echo "<h1>" . $numRows . " Row" . ($numRows == 1 ? "" : "s") . " Returned </h1>"; 

//display the results 
while($row = mssql_fetch_array($result))
{
  echo "<li>" . $row["id"] . $row["name"] . $row["year"] . "</li>";
}
//close the connection
mssql_close($dbhandle);
?>