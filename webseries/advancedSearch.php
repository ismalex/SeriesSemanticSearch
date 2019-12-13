<?php
require_once( "sparqllib.php" );

// SPARQL End-point 
$db = sparql_connect( "http://localhost:8171/WebSeries/query" );

if( !$db ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

// Define name space for your ontology
sparql_ns( "webseries","http://www.semanticweb.org/aragon/ontologies/2019/10/webseries#" );

// Initial series search SPARQL Query 
$sparql = "SELECT DISTINCT ?SeriesName  ?SeriesSeason ?Season ?totalEpisodes
	        WHERE { 
                ?Series a webseries:Series.
  ?Series webseries:Name ?SeriesName.
  ?Series webseries:hasSeason ?SeriesSeason.
  ?SeriesSeason webseries:Name ?Season.
  ?SeriesSeason webseries:hasEpisode ?Episode
  {
    SELECT  ?SeriesSeason  (COUNT (?SeriesSeason) as ?totalEpisodes)
WHERE {
  ?Series a webseries:Series.
  ?Series webseries:Name ?SeriesName.
  ?Series webseries:hasSeason ?SeriesSeason.
  ?SeriesSeason webseries:hasEpisode ?Episode.
}
    group by (?SeriesSeason ) ";
$sparql .= (isset($_GET["conditions"]) && !empty($_GET["conditions"]))?
"HAVING  (COUNT (?Series)".$_GET["conditions"]." ".$_GET["values"]. ")":"";
$sparql .=	 "}}";
//echo $sparql;
$result = sparql_query( $sparql ); 
//$result1 = sparql_query( $sparql ); 
if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
$fields = sparql_field_array( $result );



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
            <h3>Series that have </h3>
            <div class="col-md-6">
                <form name="myForm" method="GET" class="form-inline ">
                        <select name="conditions" placeholder="Type a search term." class="form-control "
                            autocomplete="off">
                            <option value=">">more than</option>
                            <option value="<">less than</option>
                            <option value="=">exactly</option>
                        
                        </select>
                        &nbsp;
                        <select name="values" placeholder="Type a search term." class="form-control "
                            autocomplete="off">
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                        <h3> &nbsp; Episodes per season.&nbsp;&nbsp;</h3>
                    </br>
                    <input type="submit" class="btn btn-outline-secondary" value="Find" />
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-1"></div>
            <?php echo sparql_num_rows( $result ); ?> results.
            <div class="col-6">
                <?php
		 while( $row = sparql_fetch_array( $result ) )
		{ 
               echo'
                <div class="border-bottom">
                    <div class="card-body">
                        <p id="'.ucfirst($row["SeriesName"]).'" class=" h5  card-title" onClick="reply_click(this.id)"> 
                        '.ucfirst($row["SeriesName"]).'</p> 
                        
                        <i> Season: '.ucfirst($row["Season"]).' ― '.ucfirst($row["totalEpisodes"]).' Episodes</i>
                        <br/>
                      
                    </div>
                </div>'; 
			}
	?>
            </div>
        </div>

        <div class="modal fade" id="modalContactForm" name="modalContactForm" tabindex="-1" role="dialog"
            aria-labelledby="myModalLabel" aria-hidden="true">
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