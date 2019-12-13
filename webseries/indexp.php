<?php
require_once( "sparqllib.php" );

// SPARQL End-point 
$db = sparql_connect( "http://localhost:8171/Places/query" );

if( !$db ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

// Define name space for your ontology
sparql_ns( "place","http://www.cs.com/places#" );

//SPARQL Query 
$sparql = "SELECT ?country_name ?city_name 
	   WHERE { 
		?country a place:Country. 
		?city place:isCityOf ?country. 
		?country place:name ?country_name. 
		?city place:name ?city_name.";
$sparql .= (isset($_GET["country"]) && !empty($_GET["country"]))?"FILTER(LCASE(STR(?country_name))=\"".strtolower($_GET["country"])."\")":"";
$sparql .=	 "}";
//echo $sparql;
$result = sparql_query( $sparql ); 
if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
 
$fields = sparql_field_array( $result );
?>

<html>
<head>
<title>Example Code</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>
<body>
<h3 class='h3 mb-3 mt-4 font-weight-normal text-center'>Search Cities for a Country</h3>

<div class='col-6 offset-3 text-center'><form method="GET"><input type="text" name="country" placeholder="Country Name" /><input type="submit" value="Search" /></form></div>
<h4 class='h4 mb-3 mt-4 font-weight-normal text-center'>Number of rows: <?php echo sparql_num_rows( $result ); ?> results.</h4>
<div class='col-6 offset-3'><table class='table table-bordered table-hover'>
<thead class='thead-light'><tr>
	<?php
		foreach( $fields as $field )
		{
			print "<th>$field</th>";
		}
		print "</tr></thead><tbody>";
		while( $row = sparql_fetch_array( $result ) )
		{
			print "<tr>";
			foreach( $fields as $field )
			{
				print "<td>$row[$field]</td>";
			}
			print "</tr>";
		}
		print "";
	?>
</tbody></table></div>
</body>
</html>
 