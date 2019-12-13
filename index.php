<?php
require_once( "sparqllib.php" );

// SPARQL End-point 
$db = sparql_connect( "http://localhost:8171/WebSeries/query" );

if( !$db ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }

// Define name space for your ontology
sparql_ns( "webseries","http://www.semanticweb.org/aragon/ontologies/2019/10/webseries#" );

// INITIAL SERIES SEARCH SPARQL QUERY 
$sparql = "SELECT  ?SeriesName ?SeriesYear ?SeriesGenre ?SeriesDescription ?totalSeasons
	        WHERE { 
                    ?Series a webseries:Series.
                    ?Series webseries:Name ?SeriesName.
                    ?Series webseries:Year ?SeriesYear.
                    ?Series webseries:Description ?SeriesDescription.
                    ?Series webseries:hasGenre ?Genre.
                    ?Genre webseries:Name ?SeriesGenre ";
$sparql .= (isset($_GET["searchTerm"]) && !empty($_GET["searchTerm"]))?"FILTER(REGEX(STR(?SeriesName),\"".strtolower($_GET["searchTerm"])."\"))":"";
$sparql .= "{
             SELECT ?Series (COUNT (?Season) as ?totalSeasons)
                WHERE {
                        ?Series a webseries:Series.
                        ?Series webseries:hasSeason ?Season.
                        ?Season webseries:Name ?SeasonName
                    }
GROUP BY (?Series) }";
$sparql .=	 "}";
//echo $sparql;
$result = sparql_query( $sparql ); 
if( !$result ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; }
$fields = sparql_field_array( $result );


// GET SEASONS OF THE SERIES AND ALL THE ESPISODES PER SEASON SPARQL QUERY 
$valueSeries="";
 $sparqlSeasons = "SELECT ?SeriesName ?Season ?EpisodeName ?EpisodeDuration
                WHERE { 
                    ?Series a webseries:Series.
                    ?Series webseries:Name ?SeriesName.
                    ?Series webseries:hasSeason ?SeriesSeason.
                    ?SeriesSeason webseries:Name ?Season.
                    ?SeriesSeason webseries:hasEpisode ?Episode.
                    ?Episode webseries:Name ?EpisodeName.
                    ?Episode webseries:Duration ?EpisodeDuration ";
$sparqlSeasons .= "FILTER(REGEX(STR(?SeriesName),\"".strtolower($valueSeries)."\"))";
$sparqlSeasons .=	 "}";
//ECHO $sparqlSeasons;
$resultEpisodes = sparql_query( $sparqlSeasons ); 
if( !$resultEpisodes ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; } 
$fieldsSeasons = sparql_field_array( $resultEpisodes );


// GET ACTORS OF THE SERIES  SEASON SPARQL QUERY 
$valueSeries="";
 $sparqlCast = "SELECT ?sName ?nCast ?ActorFName ?ActorLName
                WHERE { 
                    ?Series a webseries:Series.
                    ?Series webseries:Name ?sName.
                    ?Series webseries:hasCast ?Cast.
                    ?Cast webseries:Name ?nCast.
                    ?Cast webseries:hasMember ?Actor.
                    ?Actor webseries:FirstName ?ActorFName.
                    ?Actor webseries:LastName ?ActorLName ";
$sparqlCast .= "FILTER(REGEX(STR(?sName),\"".strtolower($valueSeries)."\"))";
$sparqlCast .=	 "}";
//ECHO $sparqlCast;
$result1 = sparql_query( $sparqlCast ); 
if( !$result1 ) { print sparql_errno() . ": " . sparql_error(). "\n"; exit; } 
$fieldsCast = sparql_field_array( $result1 );


//echo "Returned value from the function : $return_value";

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
    <title>Series Search</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script type="text/javascript">
        function reply_click(clicked_id) {
            //  var el_down = document.getElementById("showNameSeries"); 
            document.getElementById("showNameSeries").innerHTML = clicked_id;
        }
    </script>
</head>

<body>
    <div class="container-fluid">
        <div class="mb-5"></div>
        <div class="row">
            <div class="col-1"></div>
            <h3>Series</h3>
            <div class="col-md-6">
                <form name="myForm" method="GET" class="form-inline ">
                    <input type="text" class="form-control " name="searchTerm" placeholder="Type a search term."
                        autocomplete="off" autofocus />
                        &nbsp;
                    <input type="submit" class="btn btn-outline-secondary" value="Find" />
                    &nbsp;
                    <input type="" class="btn btn-outline-secondary" value="Advanced"
                        onclick="window.location='actorsSearch.php'" />
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
                        <a  id="'.ucfirst($row["SeriesName"]).'" href="" data-toggle="modal" data-target="#modalContactForm" class=" h5  card-title" onClick="reply_click(this.id)"> 
                        '.ucfirst($row["SeriesName"]).'</a> 
                        <br/>
                        <i>'.$row["SeriesYear"].'</i>
                        <br/>
                        '.ucfirst($row["SeriesGenre"]).'  &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;'.ucfirst($row["totalSeasons"]).' Seasons
                        <br/>
                        <p class="card-text ">'.$row["SeriesDescription"].'</p>
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

                        <div class="h5 card-title">
                                
                                    <span id="showNameSeries" name="showNameSeries"> </span> 
                                        <span class="font-weight-light"> - Details</span> 
                        </div>
                    </div>
                    <div class="modal-body">
                        <nav class=" nav-justified">
                            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home"
                                    role="tab" aria-controls="nav-home" aria-selected="true">Episodes</a>
                                <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile"
                                    role="tab" aria-controls="nav-profile" aria-selected="false">Cast</a>
                            </div>
                        </nav>
                        <br>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                aria-labelledby="nav-home-tab">
                                <table id="tableEpisodes" class='table table-sm table-hover'>
                                    <thead>
                                        <tr>

                                            <?php
                                            $_GET['my_value'] = 'test';
                                                            echo "<script>document.writeln(p1);</script>";
                                                            ?>   

                                            <?php   
                                                                foreach( $fieldsSeasons as $field )
                                                                {
                                                                    print "<th>$field</th>";
                                                                } 
                                                                print "</tr></thead><tbody>";
                                                               
                                                                while( $row1 = sparql_fetch_array( $resultEpisodes ) )
                                                                {
                                                                    print "<tr>";
                        
                                                                    foreach( $fieldsSeasons as $field )
                                                                    {
                                                                       // if($row1["SeriesName"] = strtolower( $valueSeries) )
                                                                        {   
                                                                            //echo $row1["SeriesName"] ." ".   $valueSeries;
                                                                            print "<td>$row1[$field]</td>";
                                                                        } 
                                                                    }
                                                                    print "</tr>";
                                                                }
                                                                print "";
                                                            ?>
                                            </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="nav-profile" role="tabpanel"
                                aria-labelledby="nav-profile-tab">
                                <table id="tableEpisodes" class='table table-sm table-hover'>
                                <thead>
                                    <tr>
                                        <?php
                                                        
                                                            foreach( $fieldsCast as $field )
                                                            {
                                                                print "<th>$field</th>";
                                                            } 
                                                            print "</tr></thead><tbody>";
                                                           
                                                            while( $row1 = sparql_fetch_array( $result1 ) )
                                                            {
                                                                print "<tr>";
                    
                                                                foreach( $fieldsCast as $field )
                                                                {
                                                                   // if($row1["SeriesName"] = strtolower( $valueSeries) )
                                                                    {   
                                                                        //echo $row1["SeriesName"] ." ".   $valueSeries;
                                                                        print "<td>$row1[$field]</td>";
                                                                    } 
                                                                }
                                                                print "</tr>";
                                                            }
                                                            print "";
                                                        ?>
                                        </tbody>
                            </table>
                            </div>
                        </div>


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