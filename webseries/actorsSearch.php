<?php
//add the sparqllib.php to our PHP project
require_once( "sparqllib.php" );

// SPARQL End-point 
$db = sparql_connect( "http://localhost:8171/WebSeries/query" );

if( !$db ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

// Define name space for your ontology
sparql_ns( "webseries","http://www.semanticweb.org/aragon/ontologies/2019/10/webseries#" );

// Initial series search SPARQL Query 
$sparql = "SELECT  ?SeriesName ?CastType ?AFirstName ?ALastName
	        WHERE { 
                    ?Series a webseries:Series.
                    ?Series webseries:Name ?SeriesName.
                    ?Series webseries:hasCast ?Cast.
                    ?Cast webseries:Name ?CastType.
                    ?Cast webseries:hasMember ?Actor.
                    ?Actor webseries:FirstName ?AFirstName.
                    ?Actor webseries:LastName ?ALastName ";
$sparql .= (isset($_GET["searchTerm"]) && !empty($_GET["searchTerm"]))?"FILTER(REGEX(STR(?AFirstName),\"".($_GET["searchTerm"])."\"))":"";
$sparql .=	 "}";
//echo $sparql;
$result = sparql_query( $sparql ); 
$result1 = sparql_query( $sparql ); 
if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
$fields = sparql_field_array( $result );


// GET ACTORS OF THE SERIES  SEASON SPARQL QUERY 
 $sparqlCast = "SELECT ?firstName ?lastname
                WHERE { 
                    ?Actor a webseries:Actor.
  ?Actor webseries:FirstName ?firstName.
?Actor webseries:LastName ?lastname ";
$sparqlCast .=	 "}";
//ECHO $sparqlCast;
$result2 = sparql_query( $sparqlCast ); 
if( !$result2 ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; } 
$fieldsCast = sparql_field_array( $result2 );


// INITIAL SPARQL Query 
/*  $sparql = "SELECT ?country_name ?city_name 
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
 
$fields = sparql_field_array( $result );  */
?>

<html>

<head>
    <title>Actors Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
    <div class="container-fluid">
        <div class="mb-5"></div>
        <div class="row">
            <div class="col-1"></div>
            <h3>Actors</h3>
            <div class="col-md-6">
                <form  name="myForm" method="GET" class="form-inline " >
                    <div class="">
                    <input type="text" list="browsers" name="searchTerm" placeholder="Type a search term." class="form-control " autocomplete="off" autofocus />
                    <?php
                       ECHO '<datalist id="browsers">';
                      while( $row1 = sparql_fetch_array( $result2 ) )
                            {  
                                ECHO ' <option value="'.ucfirst($row1["firstName"]).'"> 
                                '.ucfirst($row1["firstName"]).' '.ucfirst($row1["lastname"]).' </option>';
                            }
                            ECHO'   </datalist>'
                        ?>
                       
                        <input type="submit" class="btn btn-outline-secondary" value="Find" />
                        <input type="" class="btn btn-outline-secondary" value="Advanced"
                        onclick="window.location='advancedSearch.php'" />
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-1"></div>
            <?php echo sparql_num_rows( $result ); ?> results.
            <div class="col-6">
                <?php
                    $row2 = sparql_fetch_array( $result1 ) ;
                    //ECHO $row2["AFirstName"]); 
                echo'
                    <div >
                        <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                            <img src="profilepicture.png" alt="" height="70" width="70">
                            <span class="h5">' .ucfirst($row2["AFirstName"]).' '.$row2["ALastName"].'</span>
                         </div>
                            <div class="col-6">';
                            while( $row = sparql_fetch_array( $result ) )
                            {  
                                ECHO '<span  id="'.ucfirst($row["SeriesName"]).'" href="" class="h5"  card-title"> 
                                '.ucfirst($row["SeriesName"]).'&nbsp;</span> 
                                <i> ― '.$row["CastType"].'</i>
                                <hr>';
                            }
                    echo'</div>
                        </div> 
                    </div>
                </div>';
	            ?>
            </div>         
        </div>

        <div class="modal fade" id="modalContactForm" name="modalContactForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="m-3  ">
                        <div class="h5 card-title"> F details</div>
                    </div>
                    <div class="modal-body">
                    <p id="showNameSeries" name="searchTerm"></p> 
                        <table class='table table-sm table-hover'>
                            <thead>
                                    
                                <tr>  
                                   
                                    </tbody>
                        </table>

                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-left">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
   
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
</body>

</html>